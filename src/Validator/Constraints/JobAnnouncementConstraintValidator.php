<?php

namespace App\Validator\Constraints;

use App\Entity\JobAnnouncement;
use App\Service\JobAnnouncementExistDecider;
use App\Service\JobAnnouncementStatusDecider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class JobAnnouncementConstraintValidator extends ConstraintValidator {

    private $em;
    private $existDecider;

    public function __construct(EntityManagerInterface $em, JobAnnouncementExistDecider $existDecider)
    {
        $this->em = $em;
        $this->existDecider = $existDecider;
    }

    public function validate($jobAnnouncement, Constraint $constraint)
    {
        if (!$constraint instanceof JobAnnouncementConstraint) {
            throw new UnexpectedTypeException($constraint, JobAnnouncementConstraint::class);
        }

        /** JobAnnouncement $jobAnnouncement */
        if ($jobAnnouncement instanceof JobAnnouncement) {
            /** CIT-684: A City cannot have two Job Announcements with the same Job Title in To Do, Draft, Active, or Ended status. */
            $exists = $this->existDecider->decide($jobAnnouncement);
            if ($exists) {
                $this->context->buildViolation($constraint->jobTitleAndStatusExistsMessage)
                    ->addViolation();
            }
        }
    }
}