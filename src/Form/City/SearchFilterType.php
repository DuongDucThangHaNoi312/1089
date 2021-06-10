<?php

namespace App\Form\City;

use App\Entity\JobTitle\Lookup\JobTitleName;
use App\Entity\User\CityUser;
use App\Repository\City\CensusPopulationRepository;
use App\Repository\CityRepository;
use App\Service\JobCategoryChoiceGenerator;
use App\Service\JobTitleChoiceGenerator;
use App\Service\StateChoiceGenerator;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormEvents;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User\JobSeekerUser;
use App\Repository\City\StateRepository;
use App\Repository\City\CountyRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\City;
use App\Entity\City\County;
use Symfony\Component\Security\Core\Security;

class SearchFilterType extends AbstractType
{
    /** @var StateChoiceGenerator $stateChoiceGenerator */
    protected $stateChoiceGenerator;

    /** @var JobCategoryChoiceGenerator $jobCategoryChoiceGenerator */
    protected $jobCategoryChoiceGenerator;

    /** @var JobTitleChoiceGenerator $jobTitleChoiceGenerator */
    protected $jobTitleChoiceGenerator;

    private $cityRepository;

    private $censusPopulationRepository;

    private $em;

    private $security;

    public function __construct(StateChoiceGenerator $stateChoiceGenerator,
                                JobCategoryChoiceGenerator $jobCategoryChoiceGenerator,
                                JobTitleChoiceGenerator $jobTitleChoiceGenerator,
                                CityRepository $cityRepository,
                                CensusPopulationRepository $censusPopulationRepository,
                                EntityManagerInterface $em,
                                Security $security
    )
    {
        $this->stateChoiceGenerator = $stateChoiceGenerator;
        $this->jobCategoryChoiceGenerator = $jobCategoryChoiceGenerator;
        $this->jobTitleChoiceGenerator = $jobTitleChoiceGenerator;

        $this->cityRepository = $cityRepository;
        $this->censusPopulationRepository = $censusPopulationRepository;

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
        $user = $options['user'];
        $reset = $options['reset'];
        $dataState = [];

        if ($user instanceof JobSeekerUser && $user->getState()) {
            $dataState = $this->em->getReference(City\State::class, $user->getState()->getId());
        }
        if ($user instanceof CityUser) {
            $cityIDs[] = $user->getCity()->getId();
            $countyArray = $this->em->getRepository(City\County::class)->findForCityIDs($cityIDs);

            $stateIDs = [];
            $countyIDs = [];
            foreach ($countyArray as $county) {
                $countyIDs[] = $county['countyId'];
                $stateIDs[] = $county['stateId'];

            }

            if (count($stateIDs)) {
                $dataState = $this->em->getReference(City\State::class, $stateIDs[0]);
            }
        }

        $builder
            ->add('shouldSaveSearch', HiddenType::class, [
                'attr' => [
                    'class' => 'should-save-search'
                ]
            ])
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

    protected function addElements(FormInterface $form, $reset = null, $submitted = null, $state = null, array $counties = null, array $cities = null) {
        $user = $this->security->getUser();
        $choicesCounties = [];
        $choicesCities = [];
        $choicesJobTitleNames = [];

        if (!$submitted) {
            if ($user instanceof JobSeekerUser) {
                if ($state === null && $user->getState()) {
                    $state = $user->getState()->getId();
                }
                if ($counties === null) {
                    // free trial users limited to profile county
                    if ($user->getSubscription()->getSubscriptionPlan()->getLimitCityLinkSearchToCountyOfResidence()) {
                        $counties[] = $user->getCounty();
                    } elseif (!empty($user->getInterestedCounties()->toArray())) {
                        $stateIDs = [];
                        $interestedCounties = $user->getInterestedCounties();
                        foreach ($interestedCounties as $county) {
                            $stateIDs[] = $county->getState()->getId();
                            $counties[] = $county->getId();
                        }

                        if (count($stateIDs)) {
                            $state = $stateIDs[0];
                        }
                    } elseif ($user->getCounty()->getId()) {
                        $counties[] = $user->getCounty()->getId();
                    }
                }
            }
            if ($user instanceof CityUser) {
                $cityIDs[] = $user->getCity()->getId();
                $countyArray = $this->em->getRepository(City\County::class)->findForCityIDs($cityIDs);

                $stateIDs = [];
                $countyIDs = [];
                foreach ($countyArray as $county) {
                    $countyIDs[] = $county['countyId'];
                    $stateIDs[] = $county['stateId'];
                }

                if ($state === null && count($stateIDs)) {
                    $state = $stateIDs[0];
                }
                if ($counties === null && count($countyIDs)) {
                    $counties = $countyIDs;
                }
            }
        }

        // reset data
        if ($reset) {
            if ($user instanceof JobSeekerUser && $user->getState()) {
                $state = $this->em->getReference(City\State::class, $user->getState()->getId());
            } else {
                $state = null;
            }
            $counties = null;
            $cities   = null;
        }

        $countyRepo = $this->em->getRepository(County::class);
        $cityRepo = $this->em->getRepository(City::class);
        $jobTitleNameRepo = $this->em->getRepository(JobTitleName::class);

        if ($state) {
            $choicesCounties = $countyRepo->findActiveCountiesByState($state, true);

            $choicesCities = $cityRepo->findByState($state);
            if (!$counties && !$cities) {
                $choicesJobTitleNames = $jobTitleNameRepo->findByState($state);
            }
        } else {
            $choicesJobTitleNames = $jobTitleNameRepo->findAllVisible();
        }

        if ($counties) {
            $choicesCities = $cityRepo->findCitiesByCounties($counties, true);
            if (!$cities) {
                $choicesJobTitleNames = $jobTitleNameRepo->findByCounties($counties);
            }
        }
        if ($cities) {
            $choicesJobTitleNames = $jobTitleNameRepo->findByCities($cities);
        }

        if (!$submitted) {
            if ($user instanceof JobSeekerUser) {
                if ($user->getSubscription()->getSubscriptionPlan()->getLimitCityLinkSearchToCountyOfResidence()) {
                    $choicesCounties = $countyRepo->findByCountyIDs([$user->getCounty()->getId()], true);
                    $choicesCities = $cityRepo->findCitiesByCounties([$user->getCounty()->getId()], true);
                }
            }

            if ($user instanceof CityUser) {
                if ($user->getCity()->getSubscription()->getSubscriptionPlan()->getHasSearchCityLinksLimitation()) {
                    $countyIDs = [];

                    foreach($user->getCity()->getCounties() as $county) {
                        $countyIDs[] = $county->getId();
                    }

                    $choicesCounties = $countyRepo->findByCountyIDs($countyIDs, true);
                    $choicesCities = $cityRepo->findCitiesByCounties($countyIDs, true);
                }
            }
        }

        $maxEmployees = $this->cityRepository->getMaxEmployees($counties);
        $maxPopulation = $this->censusPopulationRepository->getMaxPopulation($counties);

        $form
            ->add('counties', EntityType::class, [
                //'query_builder' => $countyQueryBuilder,
                'choices' => $choicesCounties,
                'class' => County::class,
                'multiple' => true,
                'attr' => [
                    'class' => 'js-counties'
                ],
                'required' => false,
            ])
            ->add('cities', EntityType::class, [
                //'query_builder' => $cityQueryBuilder,
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
        ;
        if ($user instanceof JobSeekerUser || $user instanceof CityUser) {
            $form
                ->add('saved', CheckboxType::class, [
                    'label' => 'My Saved City Links',
                    'required' => false
                ])
                ->add('user', HiddenType::class, [
                    'data' => $user->getId()
                ])
            ;
        }
        $form
            ->add('employees', TextType::class, [
                'attr' => [
                    'data-min' => 0,
                    'data-max' => $maxEmployees,
                    'class' => 'js-range-slider'
                ],
                'required' => false
            ])
            ->add('population', TextType::class, [
                'attr' => [
                    'data-min' => 0,
                    'data-max' => $maxPopulation,
                    'class' => 'js-range-slider'
                ],
                'required' => false
            ])
            ->add('jobCategories', ChoiceType::class, [
                'choices' => $this->jobCategoryChoiceGenerator->generate(),
                'multiple' => true,
                'expanded' => true,
                'required' => false,
            ])
//            ->add('Search', SubmitType::class)
        ;
    }

    function onPreSetData(FormEvent $event) {
        $form = $event->getForm();
        $options = $form->getConfig()->getOptions();
        $reset = $options['reset'];
        $submitted = $options['submitted'];

        $this->addElements($form, $reset, $submitted);
    }

    function onPreSubmit(FormEvent $event) {
        $form = $event->getForm();
        $data = $event->getData();

        $reset = isset($data['reset']) ? $data['reset'] : null;
        $state = isset($data['state']) ? $data['state'] : null;
        $counties = isset($data['counties']) ? $data['counties'] : null;
        $cities = isset($data['cities']) ? $data['cities'] : null;

        $this->addElements($form, $reset, true, $state, $counties, $cities);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('user');
        $resolver->setRequired('reset');
        $resolver->setRequired('submitted');
    }

}
