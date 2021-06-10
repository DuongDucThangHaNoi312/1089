<?php

namespace App\Validator\Constraints;

use App\Entity\User\JobSeekerUser;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\PropertyAccess\Exception\UnexpectedTypeException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class JobSeekerRegistrationStep3ConstraintValidator extends ConstraintValidator
{
    private $router;
    private $request;

    public function __construct(RequestStack $requestStack, RouterInterface $router)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->router  = $router;
    }


    /**
     * @param JobSeekerUser $jobSeekerUser
     * @param Constraint $constraint
     */
    public function validate($jobSeekerUser, Constraint $constraint)
    {
        if ( ! $constraint instanceof JobSeekerRegistrationStep3Constraint) {
            throw new UnexpectedTypeException($constraint, JobSeekerRegistrationStep3Constraint::class);
        }

        if (null === $jobSeekerUser || '' === $jobSeekerUser) {
            return;
        }

        $currentUrl = $this->request->getRequestUri();

        if ($currentUrl == $this->router->generate('job_seeker_registration_step_three')
            || $currentUrl == $this->router->generate('view-saved-search-job-seeker')
        ) {


            /** @var JobSeekerUser $jobSeekerUser */
            if ( ! $jobSeekerUser->getInterestedJobType()) {
                $this->context->buildViolation($constraint->message)
                              ->atPath('interestedJobType')
                              ->addViolation();
            }

            if ($jobSeekerUser->getInterestedJobLevels()->isEmpty()) {
                $this->context->buildViolation($constraint->message)
                              ->atPath('interestedJobLevels')
                              ->addViolation();
            }

            if ($jobSeekerUser->getInterestedCounties()->isEmpty()) {
                $this->context->buildViolation($constraint->message)
                              ->atPath('interestedCounties')
                              ->addViolation();
            }

            /*** CIT-705 ***/

            $data      = $this->request->request->get('job_seeker_profile');
            $jobTitles = [];
            if (key_exists('interestedJobTitleNames', $data)) {
                $jobTitles = $data['interestedJobTitleNames'];
            }

            $generalCategories    = $data['interestedJobCategoryGenerals'];
            $notGeneralCategories = $data['interestedJobCategoryNotGenerals'];

            $generalCategories    = $generalCategories == '' ? null : $generalCategories;
            $notGeneralCategories = $notGeneralCategories == '' ? null : $notGeneralCategories;

            if ( count($jobTitles) > 0 && ($generalCategories || $notGeneralCategories)) {
                $this->context->buildViolation($constraint->searchPreferenceMessage)
                        ->atPath('interestedJobCategoryGenerals')
                        ->addViolation();
            } elseif (count($jobTitles) == 0 && !$generalCategories && !$notGeneralCategories) {
                $this->context->buildViolation($constraint->searchPreferenceEmptyMessage)
                        ->atPath('interestedJobCategoryGenerals')
                        ->addViolation();
            } elseif ( $generalCategories && !$notGeneralCategories && count($jobTitles) == 0) {
                $this->context->buildViolation($constraint->searchPreferenceSecondMessage)
                        ->atPath('interestedJobCategoryGenerals')
                        ->addViolation();
            }
        }
    }
}
