<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class JobAnnouncementApplicationDeadlineConstraint extends Constraint {
    public $message = "Application Deadline must be between Active start and end dates";

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}