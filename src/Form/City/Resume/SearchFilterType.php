<?php

namespace App\Form\City\Resume;

use App\Entity\City;
use App\Entity\City\County;
use App\Entity\JobTitle\Lookup\JobCategory;
use App\Entity\JobTitle\Lookup\JobLevel;
use App\Entity\JobTitle\Lookup\JobTitleName;
use App\Entity\JobTitle\Lookup\JobType;
use App\Entity\Resume\Lookup\DegreeType;
use App\Repository\City\StateRepository;
use App\Repository\JobTitle\Lookup\JobCategoryRepository;
use App\Repository\JobTitle\Lookup\JobLevelRepository;
use App\Repository\JobTitle\Lookup\JobTypeRepository;
use App\Repository\Resume\Lookup\DegreeTypeRepository;
use Sonata\AdminBundle\Form\Type\Filter\NumberType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormEvents;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

class SearchFilterType extends AbstractType
{
    private $em;

    private $security;


    public function __construct(
                                EntityManagerInterface $em,
                                Security $security
    )
    {
        $this->em = $em;
        $this->security = $security;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     * @throws \Doctrine\ORM\ORMException
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $reset = $options['reset'];
        $dataState = [];

        // reset data
        if($reset) {
            $dataState = [];
        }

        $builder
            ->add('state', EntityType::class, [
                'query_builder' => function (StateRepository $sr) {
                    return $sr->createQueryBuilder('s')
                        ->where('s.isActive = true')
                        ->orderBy('s.name');
                },
                'class' => City\State::class,
                'attr' => [
                    'class' => 'js-state'
                ],
                'required' => false,
                'data' => $dataState,
                'placeholder' => 'Select a State'
            ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, array($this, 'onPreSetData'));
        $builder->addEventListener(FormEvents::PRE_SUBMIT, array($this, 'onPreSubmit'));
    }

    protected function addElements(FormInterface $form, $reset = null, $state = null, array $counties = null, array $cities = null) {
        $choicesCounties = [];
        $choicesCities = [];
        $choicesJobTitleNames = [];

        // reset data
        if($reset) {
            $state = null;
            $counties = null;
            $cities = null;
        }

        if ($state) {
            $choicesCounties = $this->em->getRepository(City\County::class)->findByState($state, ['name' => 'asc']);
            $choicesCities = $this->em->getRepository(City::class)->findByState($state);
            if (!$counties && !$cities) {
                $choicesJobTitleNames = $this->em->getRepository(JobTitleName::class)->findByState($state);
            }
        } else {
            $choicesJobTitleNames = $this->em->getRepository(JobTitleName::class)->findAllVisible();
        }
        if ($counties) {
            $choicesCities = $this->em->getRepository(City::class)->findCitiesByCounties($counties);
            if (!$cities) {
                $choicesJobTitleNames = $this->em->getRepository(JobTitleName::class)->findByCounties($counties);
            }
        }
        if ($cities) {
            $choicesJobTitleNames = $this->em->getRepository(JobTitleName::class)->findByCities($cities);
        }

        $form
            ->add('counties', EntityType::class, [
                'choices' => $choicesCounties,
                'class' => County::class,
                'multiple' => true,
                'attr' => [
                    'class' => 'js-counties'
                ],
                'required' => false
            ])
            ->add('cities', EntityType::class, [
                'choices' => $choicesCities,
                'class' => City::class,
                'multiple' => true,
                'attr' => [
                    'class' => 'js-cities'
                ],
                'required' => false,
            ])
            ->add('jobTitleNames', EntityType::class, [
                'choices' => $choicesJobTitleNames,
                'class' => JobTitleName::class,
                'multiple' => true,
                'attr' => [
                    'class' => 'js-jobTitles'
                ],
                'required' => false,
                'label' => 'Job Titles'
            ])
            ->add('jobLevels', EntityType::class, [
                'query_builder' => function (JobLevelRepository $jlr) {
                    return $jlr->createQueryBuilder('jl')
                        ->orderBy('jl.name');
                },
                'class' => JobLevel::class,
                'multiple' => true,
                'expanded' => true,
                'required' => false,
            ])
            ->add('jobTypes', EntityType::class, [
                'query_builder' => function (JobTypeRepository $jtr) {
                    return $jtr->createQueryBuilder('jt')
                        ->orderBy('jt.name');
                },
                'class' => JobType::class,
                'multiple' => true,
                'expanded' => true,
                'required' => false,
            ])
            ->add('yearsOfExperience', IntegerType::class, [
                'label' => 'Years Experience',
                'attr' => ['placeholder' => 0],
                'required' => false,
                'help' => 'Enter minimum number of years of experience working for cities to narrow search results (0 will maximize search results'
            ])
            ->add('educationLevel', EntityType::class, [
                'query_builder' => function (DegreeTypeRepository $dtr) {
                    return $dtr->createQueryBuilder('dt')
                        ->orderBy('dt.name');
                },
                'class' => DegreeType::class,
                'multiple' => true,
                'expanded' => true,
                'required' => false,
            ])
            ->add('jobCategories', EntityType::class, [
                'query_builder' => function (JobCategoryRepository $jcr) {
                    return $jcr->createQueryBuilder('jc')
                        ->orderBy('jc.name');
                },
                'class' => JobCategory::class,
                'multiple' => true,
                'expanded' => true,
                'required' => false,
            ])
        ;
    }

    /**
     * @param FormEvent $event
     */
    function onPreSetData(FormEvent $event) {
        $form = $event->getForm();
        $options = $form->getConfig()->getOptions();
        $reset = $options['reset'];
        $cities = null;
        $counties = null;
        $state = null;

        $this->addElements($form, $reset, $state, $counties, $cities);
    }

    function onPreSubmit(FormEvent $event) {
        $form = $event->getForm();
        $data = $event->getData();

        $reset = isset($data['reset']) ? $data['reset'] : null;
        $state = isset($data['state']) && $data['state'] ? $data['state'] : null;
        $counties = isset($data['counties']) ? $data['counties'] : null;
        $cities = isset($data['cities']) ? $data['cities'] : null;

        $this->addElements($form, $reset, $state, $counties, $cities);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('reset');
    }


}