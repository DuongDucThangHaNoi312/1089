<?php

namespace App\Form\Job;

use App\Entity\City;
use App\Entity\City\County;
use App\Entity\JobTitle\Lookup\JobCategory;
use App\Entity\JobTitle\Lookup\JobLevel;
use App\Entity\JobTitle\Lookup\JobTitleName;
use App\Entity\JobTitle\Lookup\JobType;
use App\Entity\User\JobSeekerUser;
use App\Repository\City\StateRepository;
use App\Repository\JobTitle\Lookup\JobCategoryRepository;
use App\Repository\JobTitle\Lookup\JobLevelRepository;
use App\Repository\JobTitle\Lookup\JobTypeRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormEvents;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\City\CensusPopulationRepository;
use App\Repository\CityRepository;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

class SearchFilterType extends AbstractType
{
    private $cityRepository;

    private $censusPopulationRepository;

    private $em;

    private $security;

    public function __construct(CityRepository $cityRepository,
                                CensusPopulationRepository $censusPopulationRepository,
                                EntityManagerInterface $em,
                                Security $security
    )
    {
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
        $city = $options['city'];
        $searchFilter = $options['searchFilter'];
        $dataState = [];

        // if accessing from city search link, get state from city
        if ($city) {
            $city = $this->em->getRepository(City::class)->find($city);
            if ($city && count($city->getCounties()) > 0) {
                $dataState = $city->getCounties()[0]->getState();
            }
        } elseif ($user instanceof JobSeekerUser && $user->getState()) {
            // else, default state to user's profile
            $dataState = $this->em->getReference(City\State::class, $user->getState()->getId());
        }

        if (isset($searchFilter['state'])) {
            $stateRepo = $this->em->getRepository(City\State::class);
            $dataState = $stateRepo->find($searchFilter['state']);
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
                    'class' => 'js-state select2-state'
                ],
                'required' => false,
                'data' => $dataState,
                'placeholder' => 'Select a State'
            ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, array($this, 'onPreSetData'));
        $builder->addEventListener(FormEvents::PRE_SUBMIT, array($this, 'onPreSubmit'));
    }

    protected function addElements(FormInterface $form, $reset = null, $submitted = null, $state = null, array $counties = null, array $cities = null, array $searchFilter = null) {
        $user = $this->security->getUser();
        $choicesCounties = [];
        $choicesCities = [];
        $choicesJobTitleNames = [];

        if (!$submitted) {
            /*** GET DEFAULT STATE & COUNTY FROM USER PROFILE (DEFAULT SAVED SEARCH) ***/
            if ($user instanceof JobSeekerUser) {
                if ($state === null && $user->getState() && $user->getState()->getId()) {
                    $state = $user->getState()->getId();
                }
                if ($counties === null) {
                    if (!empty($user->getInterestedCounties()->toArray())) {
                        $stateIDs = [];
                        $interestedCounties = $user->getInterestedCounties();
                        foreach ($interestedCounties as $county) {
                            $stateIDs[] = $county->getState()->getId();
                            $counties[] = $county->getId();
                        }

                        if (count($stateIDs)) {
                            $state = $stateIDs[0];
                        }
                    } elseif ($user->getCounty() && $user->getCounty()->getId()) {
                        $counties[] = $user->getCounty()->getId();
                    }
                }
            }
        }

        // reset data
        if($reset) {
            if ($user instanceof JobSeekerUser && $user->getState()) {
                $state    = $this->em->getReference(City\State::class, $user->getState()->getId());
            } else {
                $state = null;
            }
            $counties = null;
            $cities   = null;
        }

        $stateRepo  = $this->em->getRepository(City\State::class);
        $countyRepo = $this->em->getRepository(County::class);
        $cityRepo = $this->em->getRepository(City::class);
        $jobTitleNameRepo = $this->em->getRepository(JobTitleName::class);

        if (isset($searchFilter['state'])) {
            $state = $stateRepo->find($searchFilter['state']);
        }
        if (isset($searchFilter['counties']) && count($searchFilter['counties'])) {
            $counties = $searchFilter['counties'];
            $choicesCounties = $countyRepo->findBy(['id' => $searchFilter['counties']]);
        } else {
            // $searchFilter is null => get default county & state from user profile
            if ($searchFilter != null) {
                $counties = null;
            }
        }

        if (isset($searchFilter['jobTitleNames']) && count($searchFilter['jobTitleNames'])) {
            $choicesJobTitleNames = $jobTitleNameRepo->findBy(['id' => $searchFilter['jobTitleNames']]);
        }

        if ($state) {
            $choicesCounties = $countyRepo->findBy([
                'state'    => $state,
                'isActive' => true,
            ], ['name' => 'asc']);

            $choicesCities = $cityRepo->findByState($state);
            if (!$counties && !$cities) {
                $choicesJobTitleNames = $jobTitleNameRepo->findByState($state);
            }
        } else {
            $choicesJobTitleNames = $jobTitleNameRepo->findAllVisible();
        }
        
        if ($counties) {
            $choicesCities = $cityRepo->findCitiesByCounties($counties);
            if (!$cities) {
                $choicesJobTitleNames = $jobTitleNameRepo->findByCounties($counties);
            }
        }

        if ($cities) {
            $choicesJobTitleNames = $jobTitleNameRepo->findByCities($cities);
        }

        $maxEmployees = $this->cityRepository->getMaxEmployees($counties);
        $maxPopulation = $this->censusPopulationRepository->getMaxPopulation($counties);

        $form
            ->add('counties', EntityType::class, [
                'choices' => $choicesCounties,
                'class' => County::class,
                'multiple' => true,
                'attr' => [
                    'class' => 'js-counties select2-counties'
                ],
                'required' => false
            ])
            ->add('cities', EntityType::class, [
                'choices' => $choicesCities,
                'class' => City::class,
                'multiple' => true,
                'attr' => [
                    'class' => 'js-cities select2-cities'
                ],
                'required' => false,
            ])
            ->add('jobTitleNames', EntityType::class, [
                'choices' => $choicesJobTitleNames,
                'class' => JobTitleName::class,
                'multiple' => true,
                'attr' => [
                    'class' => 'js-jobTitleNames select2-jobTitleNames'
                ],
                'required' => false,
                'label' => 'Job Titles'
            ]);
        if ($user instanceof JobSeekerUser) {
            $form
                ->add('user', HiddenType::class, [
                    'data' => $user->getId()
                ])
                ->add('saved', CheckboxType::class, [
                    'label' => 'Saved Jobs of Interest/Job Alerts',
                    'required' => false
                ])
                ->add('searchSubmittedJobTitle', CheckboxType::class, [
                    'label' => 'Jobs I\'ve Submitted Interest In',
                    'required' => false
                ])
            ;
        }
        $form
            ->add('population', TextType::class, [
                'attr' => [
                    'data-min' => 0,
                    'data-max' => $maxPopulation,
                    'class' => 'js-range-slider'
                ],
                'required' => false
            ])
            ->add('employees', TextType::class, [
                'attr' => [
                    'data-min' => 0,
                    'data-max' => $maxEmployees,
                    'class' => 'js-range-slider'
                ],
                'required' => false
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
            ->add('jobCategories', EntityType::class, [
                'query_builder' => function (JobCategoryRepository $jcr) {
                    return $jcr->createQueryBuilder('jc')
                               ->orderBy('jc.name');
                },
                'class' => JobCategory::class,
                'multiple' => true,
                'expanded' => true,
                'required' => false,
                'attr'     => [
                    'class' => 'search-filter-job-categories'
                ],
                'label_attr' => ['class' => 'job-categories tip']
            ])
        ;
    }

    /**
     * @param FormEvent $event
     * @throws \Doctrine\ORM\ORMException
     */
    function onPreSetData(FormEvent $event) {
        $form = $event->getForm();
        $options = $form->getConfig()->getOptions();

        $searchFilter = $options['searchFilter'];
        $reset = $options['reset'];
        $submitted = $options['submitted'];
        $city = $options['city'];
        $cities = null;
        $counties = null;
        $state = null;

        if ($city) {
            $city = $this->em->getRepository(City::class)->find($city);
            if ($city) {
                foreach ($city->getCounties() as $c) {
                    $counties[] = $c;
                }
                $state = $city->getCounties()[0]->getState();
                $cities[] = $city;
            }
        }

        $this->addElements($form, $reset, $submitted, $state, $counties, $cities, $searchFilter);
    }

    function onPreSubmit(FormEvent $event) {
        $form = $event->getForm();
        $data = $event->getData();
        $options = $form->getConfig()->getOptions();

        $searchFilter = $options['searchFilter'];

        $reset = isset($data['reset']) ? $data['reset'] : null;
        $state = isset($data['state']) && $data['state'] ? $data['state'] : null;
        $counties = isset($data['counties']) ? $data['counties'] : null;
        $cities = isset($data['cities']) ? $data['cities'] : null;

        $this->addElements($form, $reset, true, $state, $counties, $cities, $searchFilter);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('user');
        $resolver->setRequired('reset');
        $resolver->setRequired('submitted');
        $resolver->setRequired('city');
        $resolver->setRequired('searchFilter');

        $resolver->setDefaults([
            'csrf_protection' => false,
        ]);
    }

}
