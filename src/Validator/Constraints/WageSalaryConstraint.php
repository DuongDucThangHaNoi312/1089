<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class WageSalaryConstraint extends Constraint {
    public $message = 'High and Low Wage/Salary ranges must be specified or select Depends on Qualifications';
    public $rangeMessage = 'Low Wage/Salary must be higher than High Wage/Salary';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }


}