<?php

namespace App\Validator\Constraints;

use App\Entity\JobAnnouncement;
use App\Service\JobAnnouncementStatusDecider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class JobAnnouncementDetailsConstraintValidator extends ConstraintValidator {

    private $em;
    private $statusDecider;

    public function __construct(EntityManagerInterface $em, JobAnnouncementStatusDecider $statusDecider)
    {
        $this->em = $em;
        $this->statusDecider = $statusDecider;
    }

    public function validate($jobAnnouncement, Constraint $constraint)
    {
        if (!$constraint instanceof JobAnnouncementDetailsConstraint) {
            throw new UnexpectedTypeException($constraint, JobAnnouncementDetailsConstraint::class);
        }

        /** JobAnnouncement $jobAnnouncement */
        if ($jobAnnouncement instanceof JobAnnouncement) {
            if ($jobAnnouncement->getIsAlert() == true) {
                if ($jobAnnouncement->getApplicationUrl() == null) {
                    $this->context->buildViolation($constraint->jobAlertLinkMessage)
                        ->atPath('applicationUrl')
                        ->addViolation();
                }
            } else {
                if ($jobAnnouncement->getApplicationUrl() == null) {
                    $this->context->buildViolation($constraint->applicationLinkMessage)
                        ->atPath('applicationUrl')
                        ->addViolation();
                }

                if ($jobAnnouncement->getDescription() == null || $jobAnnouncement->getDescription() == null) {
                    $this->context->buildViolation($constraint->descriptionLinkMessage)
                        ->atPath('description')
                        ->addViolation();
                }
            }
        }
    }
}