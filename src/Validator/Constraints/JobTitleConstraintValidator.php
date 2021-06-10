<?php

namespace App\Validator\Constraints;

use App\Entity\City\JobTitle;
use App\Entity\User\CityUser;
use App\Service\JobTitleML;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class JobTitleConstraintValidator extends ConstraintValidator
{

    private $jobTitleML;
    private $em;
    private $translator;
    private $request;
    private $security;

    public function __construct(JobTitleML $jobTitleML, EntityManagerInterface $em, TranslatorInterface $translator, RequestStack $requestStack, Security $security)
    {
        $this->jobTitleML = $jobTitleML;
        $this->em         = $em;
        $this->translator = $translator;
        $this->request    = $requestStack->getCurrentRequest();
        $this->security   = $security;
    }

    /**
     * @param JobTitle $jobTitle
     * @param Constraint $constraint
     *
     * @throws \Phpml\Exception\FileException
     * @throws \Phpml\Exception\SerializeException
     */
    public function validate($jobTitle, Constraint $constraint)
    {
        if ( ! $constraint instanceof JobTitleConstraint) {
            throw new UnexpectedTypeException($constraint, JobTitleConstraint::class);
        }

        if ($jobTitle instanceof JobTitle) {
            $jobTitleName = $jobTitle->getJobTitleName();
            $city         = $jobTitle->getCity();
            $user         = $this->security->getUser();

            if ( ! $jobTitle->getJobTitleName() && ($user instanceof CityUser)) {
                $jobTitleName = $this->request->request->get('job_title')['name'];
                $city = $user->getCity();
            } else {
                $jobTitleName = $jobTitle->getJobTitleName()->getName();
            }

            $existed = $this->em->getRepository(JobTitle::class)->findDuplidateJobTitle(
                $jobTitleName,
                $city,
                $jobTitle->getDepartment(),
                $jobTitle->getType()
            );

            if ($existed) {
                if ( ! $jobTitle->getId()) {
                    $this->context->buildViolation($this->translator->trans('job_title.create.duplicated', ['%jobTitleName%' => $jobTitle->getJobTitleName()]))
                                  ->addViolation();
                } elseif ($jobTitle->getId() && $jobTitle->getId() != $existed[0]->getId()) {
                    $this->context->buildViolation($this->translator->trans('job_title.create.duplicated', ['%jobTitleName%' => $jobTitle->getJobTitleName()]))
                                  ->addViolation();
                }
            }

        }
    }
}