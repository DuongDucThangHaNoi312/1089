<?php

namespace App\Validator\Constraints;

use App\Entity\JobAnnouncement;
use App\Service\JobAnnouncementStatusDecider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class JobAnnouncementScheduledDatesConstraintValidator extends ConstraintValidator {

    /**
     * @var JobAnnouncementStatusDecider $statusDecider
     */
    protected $statusDecider;

    protected $authorizationChecker;

    private $em;

    public function __construct(JobAnnouncementStatusDecider $statusDecider, AuthorizationCheckerInterface $authorizationChecker, EntityManagerInterface $em)
    {
        $this->statusDecider = $statusDecider;
        $this->authorizationChecker = $authorizationChecker;
        $this->em = $em;
    }

    public function validate($jobAnnouncement, Constraint $constraint)
    {
        if (!$constraint instanceof JobAnnouncementScheduledDatesConstraint) {
            throw new UnexpectedTypeException($constraint, JobAnnouncementScheduledDatesConstraint::class);
        }

        /** JobAnnouncement $jobAnnouncement */
        if ($jobAnnouncement instanceof JobAnnouncement) {
            /** @var JobAnnouncement\Lookup\JobAnnouncementStatus $status */
            $status = $this->statusDecider->decide($jobAnnouncement);
            if ($status->getId() == JobAnnouncement::STATUS_ACTIVE || $status->getId() == JobAnnouncement::STATUS_SCHEDULED) {
                $city = $jobAnnouncement->getJobTitle()->getCity();
                $jaRepo = $this->em->getRepository(JobAnnouncement::class);
                $isCityUser = $this->authorizationChecker->isGranted('ROLE_CITYUSER');
                if ($isCityUser == true) {
                    if ($city->getSubscription()->getSubscriptionPlan()->getAllowedActiveJobPostings() && $jaRepo->getCountActiveJobAnnouncementsForCity($city) >= $city->getSubscription()->getSubscriptionPlan()->getAllowedActiveJobPostings()) {
                        $isCityAdmin = $this->authorizationChecker->isGranted('ROLE_CITYADMIN');
                        $this->context->buildViolation($constraint->getJobAnnouncementLimit($jobAnnouncement->getIsAlert() ? 'alert' : 'announcement', $isCityAdmin))
                            ->addViolation();
                    }
                    if ($jobAnnouncement->checkIsComplete() == false) {
                        $this->context->buildViolation($constraint->getScheduledDatesMessage($jobAnnouncement->getIsAlert() ? 'alert' : 'announcement'))
                            ->addViolation();
                    }
                } else {
                    if ($jobAnnouncement->checkIsComplete() == false) {
                        $this->context->buildViolation($constraint->getScheduledDatesMessage($jobAnnouncement->getIsAlert() ? 'alert' : 'announcement'))
                            ->addViolation();
                    }
                }
            }

            if ($jobAnnouncement->getHasNoEndDate() == false) {
                if ($jobAnnouncement->getEndsOn() != null && $jobAnnouncement->getEndsOn() < $jobAnnouncement->getApplicationDeadline()) {
                    $this->context->buildViolation($constraint->applicationDeadlineEndsOnMessage)
                        ->addViolation();
                }

                if ($jobAnnouncement->getStartsOn() > $jobAnnouncement->getApplicationDeadline()) {
                    $this->context->buildViolation($constraint->applicationDeadlineStartsOnMessage)
                        ->addViolation();
                }

                if ($jobAnnouncement->getEndsOn() == null || $jobAnnouncement->getEndsOn() <= $jobAnnouncement->getStartsOn()) {
                    $this->context->buildViolation($constraint->endsOnStartsOnMessage)
                        ->addViolation();
                }
            }
        }
    }
}