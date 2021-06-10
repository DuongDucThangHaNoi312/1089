<?php

namespace App\Validator\Constraints;

use App\Entity\JobAnnouncement;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class JobAnnouncementApplicationDeadlineConstraintValidator extends ConstraintValidator {
    public function validate($jobAnnouncement, Constraint $constraint)
    {
        if (!$constraint instanceof JobAnnouncementApplicationDeadlineConstraint) {
            throw new UnexpectedTypeException($constraint, JobAnnouncementApplicationDeadlineConstraint::class);
        }


        if ($jobAnnouncement instanceof JobAnnouncement) {
            if ($jobAnnouncement->getHasNoEndDate() == false) {
                if ($jobAnnouncement->getStartsOn() != null && $jobAnnouncement->getEndsOn() != null) {
                    if ($jobAnnouncement->getApplicationDeadline() != null && $jobAnnouncement->getApplicationDeadline() > $jobAnnouncement->getEndsOn()) {
                        $this->context->buildViolation($constraint->message)
                            ->atPath('applicationDeadline')
                            ->addViolation();
                    }
                }
            }
        }


    }
}