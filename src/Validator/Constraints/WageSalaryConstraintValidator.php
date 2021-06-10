<?php

namespace App\Validator\Constraints;

use App\Entity\JobAnnouncement;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class WageSalaryConstraintValidator extends ConstraintValidator {
    public function validate($jobAnnouncement, Constraint $constraint)
    {
        if (!$constraint instanceof WageSalaryConstraint) {
            throw new UnexpectedTypeException($constraint, WageSalaryConstraint::class);
        }

        /** JobAnnouncement $jobAnnouncement */
        if ($jobAnnouncement instanceof JobAnnouncement) {
            if ($jobAnnouncement->getWageRangeDependsOnQualifications() != true) {
                if ($jobAnnouncement->getWageSalaryLow() == null || $jobAnnouncement->getWageSalaryHigh() == null || $jobAnnouncement->getWageSalaryUnit() == null) {
                    $this->context->buildViolation($constraint->message)
                        ->atPath('')
                        ->addViolation();
                } elseif ($jobAnnouncement->getWageSalaryLow() > $jobAnnouncement->getWageSalaryHigh()) {
                    $this->context->buildViolation($constraint->rangeMessage)
                        ->atPath('')
                        ->addViolation();
                }
            }
        }
    }
}