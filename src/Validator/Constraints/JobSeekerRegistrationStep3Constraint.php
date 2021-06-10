<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class JobSeekerRegistrationStep3Constraint extends Constraint
{
    /*
     * Any public properties become valid options for the annotation.
     * Then, use these in your validator class.
     */
    public $message = 'This value should not be blank';

    public $searchPreferenceMessage = 'You can only search by “Job Title” or “Category”. Please  adjust your selections and try again';

    public $searchPreferenceEmptyMessage = 'Please choose how you would like to search for Jobs';

    public $searchPreferenceSecondMessage = 'Please make sure you’ve selected a second category of interest';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
