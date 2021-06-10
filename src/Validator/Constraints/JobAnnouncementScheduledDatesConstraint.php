<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class
JobAnnouncementScheduledDatesConstraint extends Constraint {
    public function getScheduledDatesMessage($type = "announcement") {
        return sprintf('Please complete all sections before scheduling or activating a job %s', $type);
    }

    public function getJobAnnouncementLimit($type = "announcement", $isCityAdmin = false) {
        if (!$isCityAdmin) {
            return sprintf("Your subscription does not allow you to schedule or activate any more job %ss. Alert your City Admin to update your city's subscription.", $type);
        }
        return sprintf("Your subscription does not allow you to schedule or activate any more job %ss. Update your city subscription to increase your limit.", $type);
    }

    public $applicationDeadlineEndsOnMessage = "Ends Date must be greater than or equal to the Application Deadline";
    public $applicationDeadlineStartsOnMessage = "Start Date must be less than or equal to the Application Deadline";
    public $endsOnStartsOnMessage = "Ends Date must be greater than or equal to the Start Date";

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}