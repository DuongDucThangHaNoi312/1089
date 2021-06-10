<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class JobAnnouncementConstraint extends Constraint {
    public $jobTitleAndStatusExistsMessage = "There’s another Active Job Alert with the same Job Title in this City. \n\n You cannot have more than one Job Alert with the same Job Title in Todo, Draft, Active or Ended.";

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}