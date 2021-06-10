<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class JobAnnouncementDetailsConstraint extends Constraint {
    public $applicationLinkMessage = 'Application Link is required.';
    public $descriptionLinkMessage = 'Description is required';
    public $jobAlertLinkMessage = 'Job Alert Link cannot be blank.';
    public $jobTitleAndStatusExistedMessage = 'The Job Announcement with given Job Title and Status already exists in this City';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}