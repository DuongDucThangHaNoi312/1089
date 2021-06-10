<?php

namespace App\Admin;

use App\Entity\City;
use App\Entity\City\County;
use App\Entity\City\Department;
use App\Entity\City\JobTitle;
use App\Entity\City\State;
use App\Entity\User\JobSeekerUser\SubmittedJobTitleInterest;
use App\Repository\City\CountyRepository;
use App\Repository\City\DepartmentRepository;
use App\Repository\City\StateRepository;
use App\Repository\User\JobSeekerUser\SubmittedJobTitleInterestRepository;
use Exception;
use Sonata\DoctrineORMAdminBundle\Filter\ModelAutocompleteFilter;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\CoreBundle\Form\Type\CollectionType;
use Sonata\DoctrineORMAdminBundle\Filter\CallbackFilter;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Vich\UploaderBundle\Form\Type\VichImageType;

class CityAdmin extends AbstractAdmin
{
    protected $datagridValues = [
        '_sort_order' => 'ASC',
        '_sort_by' => 'name',
    ];

//    public function createQuery($context = 'list')
//    {
//        $repository = $this->modelManager->getEntityManager($this->getClass())->getRepository($this->getClass());
//        $query = new ProxyQuery($repository->getAdminQueryBuilder());
//
//        foreach ($this->extensions as $extension) {
//            $extension->configureQuery($this, $query, $context);
//        }
//
//        return $query;
//    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        /* @var $modelManager \Sonata\DoctrineORMAdminBundle\Model\ModelManager */
        $modelManager = $this->getModelManager();
        /* @var $countyRepository \App\Repository\City\CountyRepository */
        $countyRepository = $modelManager->getEntityManager($this->getClass())->getRepository(County::class);
        $datagridMapper
            ->add('name', null, [
                'label' => 'Filter by City Name',
                'show_filter' => true,
            ])
            ->add('counties', ModelAutocompleteFilter::class, [
                'label' => 'Filter by County',
                'show_filter' => true,
            ], null, [
                'property' => 'name',
                'to_string_callback' => function($entity) {
                    return $entity->getDisplayName();
                },
                // Added query_builder to filter by county name A-Z
//                'query_builder' => $countyRepository->getQueryBuilderToOrderByName()
            ])
            ->add('stateFromCounty', CallbackFilter::class, [
                'label' => 'Filter by State',
                'show_filter' => true,
                'callback' => [$this, 'getStateFromCounty'],
                'field_type' => EntityType::class,
                'field_options' => [
                    "class" => State::class,
                    'query_builder' => function (StateRepository $sr) {
                        return $sr->createQueryBuilder('s')
                            ->orderBy('s.name');
                    }

                ],
            ])
            ->add('cgjPostsJobs')
            ->add('isSuspended')
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('prefix', null, [
                'required' => true
            ])
            ->add('name', null, [
                'label' => 'City Name',
            ])
            ->add('slug')
            ->add('counties')
            ->add('stateFromCounty', 'array',[
                'label' => 'State',
                'template' => 'admin/list/custom_state_from_city_list.html.twig'
            ])
            ->add('updatedAt', null, [
                'label' => 'Last Updated',
                'format' => 'm/d/Y',
            ])
            ->add('passcode', null, [
                'editable' => true
            ])
            ->add('countOfUsersWhoSubmittedInterest', null, [
                'label' => 'Users Expressing Interest',
                'template' => 'admin/list/city_interested_user_count.html.twig'
            ])
//            ->add('cntInterestedUser')
            ->add('_action', null, [
                'actions' => [
                    'show' => [],
                    'edit' => [],
                    'delete' => [],
                ],
            ])
        ;
    }

    public function getSubject()
    {
        /** @var City $subject */
        $subject = parent::getSubject();

        if ($subject) {
            $subject->setTempState($subject->getState());
        }

        return $subject;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $subject = $this->getSubject();
        $cityId = $subject->getId();

        $routeIds = [
            'parent_id' => $cityId,
        ];

        /* @var $modelManager \Sonata\DoctrineORMAdminBundle\Model\ModelManager */
        $modelManager = $this->getModelManager();
        /* @var $jobTitleRepository \App\Repository\City\JobTitleRepository */
        $jobTitleRepository = $modelManager->getEntityManager($this->getClass())->getRepository(JobTitle::class);

        $formMapper
            ->tab('City Information')
                ->with('City Information', [
                    'class' => 'col-md-6',
                    'box_class' => 'box',
                    'start_row' => true,
                ])
                    ->add('tempState', EntityType::class, [
                        'query_builder' => function (StateRepository $sr) {
                            return $sr->createQueryBuilder('s')
                                      //->where('s.isActive = true')
                                      ->orderBy('s.name');
                        },
                        'attr'               => [
                            'class' => 'city-admin-state',
                        ],
                        'placeholder' => 'Select a State',
                        'class' => State::class,
                        'label' => 'State',
                        'required' => true,
                        'disabled' => $cityId ? true : false,
                    ], ['custom_field_size' => 4])
                    ->add('counties', null, [], ['custom_field_size' => 8])
                    ->add('prefix', null, [], ['custom_field_size' => 4])
                    ->add('name', null, [], ['custom_field_size' => 8])
                    ->add('address', null, [
                        'label' => 'Street Address'
                    ], ['custom_field_size' => 8])
                    ->add('zipCode', null, [], ['custom_field_size' => 4])

                    ->add('cityHallPhone', null, [], ['custom_field_size' => 4])
                    ->add('mainWebsite', null, [], ['custom_field_size' => 4])
                    ->add('adminCityUser', null, [], ['custom_field_size' => 4])
                ->end()

                ->with('Profile Status', [
                    'class' => 'col-md-6',
                    'box_class' => 'box',
                    'end_row' => true,
                ])
                    ->add('isRegistered', null, [
                        'label' => 'Registered'
                    ], ['custom_field_size' => 3])
                    ->add('isValidated', null, [
                        'label' => 'Validated'
                    ], ['custom_field_size' => 3])
                    ->add('isSuspended', null, [
                        'label' => 'Suspended'
                    ], ['custom_field_size' => 3])
                    ->add('doesCityAllowChanges', null, [
                        'label' => 'Allows Changes'
                    ], ['custom_field_size' => 3])
                    ->add('allowsJobAnnouncements', null, [
                        'label' => 'Allows Job Announcements'
                    ], ['custom_field_size' => 6])
                    ->add('cgjPostsJobs', null, [
                        'label' => 'CGJ Posts Jobs'
                    ], ['custom_field_size' => 3])
                    ->add('createdAt', DateType::class, [
                        'label' => 'City Added Date',
                        'disabled' => true,
                        'required' => false,
                        'widget' => 'single_text',
                        'format' => 'M/d/Y',
                    ], ['custom_field_size' => 4])
                    ->add('profileAddedDate', DateType::class, [
                        'label' => 'Profile Added',
                        'disabled' => true,
                        'required' => false,
                        'widget' => 'single_text',
                        'format' => 'M/d/Y',
                    ], ['custom_field_size' => 4])
                    ->add('jobTitlesAddedDate', DateType::class, [
                        'label' => 'Job Titles Added',
                        'disabled' => true,
                        'required' => false,
                        'widget' => 'single_text',
                        'format' => 'M/d/Y',
                    ], ['custom_field_size' => 4])
                    ->add('updatedAt', DateType::class, [
                        'label' => 'Last Updated',
                        'disabled' => true,
                        'required' => false,
                        'widget' => 'single_text',
                        'format' => 'M/d/Y',
                    ], ['custom_field_size' => 4])
                    ->add('suspensionEmailSentAt', DateType::class, [
                        'label' => 'Suspension Email Sent',
                        'disabled' => true,
                        'required' => false,
                        'widget' => 'single_text',
                        'format' => 'M/d/Y',
                    ], ['custom_field_size' => 4])
                    ->add('urlLastTestedDate', TextType::class, [
                        'label' => 'URL Last Tested',
                        'required' => false,
                        'disabled' => true,
                    ], ['custom_field_size' => 4, 'end_row' => true])
                    ->add('countFTE', null, [
                        'label' => 'FTE Count'
                    ], ['custom_field_size' => 4])
                    ->add('countJobTitles', IntegerType::class, [
                        'required' => false,
                        'disabled' => true,
                        'label' => 'Total Job Titles',
                        'data' => $jobTitleRepository->getJobCountByCity($cityId),
                    ], ['custom_field_size' => 4])
                    ->add('sealImageFile', VichImageType::class, [
                        'label' => 'Seal Image',
                        'required' => false,
                        'download_link' => false,
                    ])
                    ->add('bannerImageFile', VichImageType::class, [
                        'label' => 'Banner Image',
                        'required' => false,
                        'download_link' => false,
                    ])
                ->end()

                ->with('City Operation Hours', [
                    'class' => 'col-md-6',
                    'box_class' => 'box',
                ])
                    ->add('operationHours', CollectionType::class, [
                        'required' => false,
                        'by_reference' => true,
                        'label' => false,
                    ], [
                        'edit' => 'inline',
                        'inline' => 'table',
                    ])
                    ->add('timezone', null, [], ['custom_field_size' => 4])
                    ->add('timezoneSummer', null, [], ['custom_field_size' => 4])
                    ->add('hoursDescription', null, [], ['custom_field_size' => 6])
                    ->add('hoursDescriptionOther', null, [], ['custom_field_size' => 6])
                ->end()

                ->with('City Statistics', [
                    'class' => 'col-md-6',
                    'box_class' => 'box',
                ])
                    ->add('yearFounded', null, [], ['custom_field_size' => 3])
                    ->add('yearChartered', null, [], ['custom_field_size' => 3])
                    ->add('yearIncorporated', null, [], ['custom_field_size' => 3])
                    ->add('squareMiles', null, [], ['custom_field_size' => 3])
                    ->add('censusPopulations', CollectionType::class, [
                        'required' => false,
                        'by_reference' => true,
                    ], [
                        'edit' => 'inline',
                        'inline' => 'table',
                    ])
                ->end()

                ->with('Registration Information', [
                    'class' => 'col-md-6',
                    'box_class' => 'box',
                ])
                    ->add('passcode', null, [], ['custom_field_size' => 4])
                ->end()

            ->end()

            ->tab('City Urls')
                ->with('City URLs', [
                    'class' => 'col-md-12',
                    'box_class' => 'box',
                ])
                    ->add('urls', CollectionType::class, [
                        'required' => false,
                        'by_reference' => true,
                        'label' => false,
                    ], [
                        'edit' => 'inline',
                        'inline' => 'table',
                    ])
                ->end()
            ->end()

            ->tab('Manage Departments')
                ->with('Add/Edit Departments', [
                    'class' => 'col-md-12 sortable-departments',
                    'box_class' => 'box',
                ])
                    ->add('departments', CollectionType::class, [
                        'required' => false,
                        'by_reference' => true,
                        'label' => false,
                    ], [
                        'edit' => 'inline',
                        'inline' => 'table',
                        'sortable' => 'position',
                    ])
                ->end()
            ->end()

            ->tab('HR Information')
                ->with("Human Resource Director's Information", [
                    'class' => 'col-md-12',
                    'box_class' => 'box',
                ])
                    ->add('hrNamePrefix', null, [
                        'label' => 'Prefix'
                    ], ['custom_field_size' => 2])
                    ->add('hrDirectorFirstName', null, [
                        'label' => 'Director First Name'
                    ], ['custom_field_size' => 4])
                    ->add('hrDirectorLastName', null, [
                        'label' => 'Director Last Name'
                    ], ['custom_field_size' => 4])
                    ->add('hrNameSuffix', null, [
                        'label' => 'Suffix'
                    ], ['custom_field_size' => 2])
                    ->add('hrDirectorTitle', null, [
                        'label' => 'Director Title'
                    ], ['custom_field_size' => 4])
                    ->add('hrDirectorPhone', null, [
                        'label' => 'Director Phone'
                    ], ['custom_field_size' => 4])
                    ->add('hrDirectorEmail', null, [
                        'label' => 'Director Email'
                    ], ['custom_field_size' => 4])
                ->end()
            ->end()
            ->tab('City Profile Text')
                ->with('Profile Title and About Text', [
                    'class' => 'col-md-12',
                    'box_class' => 'box',
                ])
                    ->add('profileTitle')
                    ->add('profileAbout')
                    ->add('profileAbout', TextareaType::class, [
                        'required' => false,
                        'attr'     => [
                            'class' => 'ckeditor'
                        ]
                    ])
                ->end()
            ->end()
        ;


        // LOAD COUNTIES BY STATE

        /** @var CountyRepository $countyRepo */
        $countyRepo = $this->getModelManager()->getEntityManager($this->getClass())->getRepository(County::class);

        $builder      = $formMapper->getFormBuilder();
        $formModifier = function (FormInterface $form, State $state = null) use ($countyRepo, $cityId, $subject) {
            $counties = [];
            if ($state) {
                $counties = $countyRepo->findCountiesByState($state->getId(), true);
            }

            $form->add('counties', EntityType::class, [
                'auto_initialize' => false,
                'by_reference' => false,
                'multiple'     => true,
                'required'     => true,
                'disabled'     => $cityId ? true : false,
                'class'        => County::class,
                'choices'      => $counties,
                'choice_label'     => 'getDisplayName',
                'attr'         => [
                    'class' => 'city-admin-counties'
                ]
            ], ['custom_field_size' => 8]);
        };

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($formModifier) {
                $city  = $event->getData();
                $state = $city && $city->getState() ? $city->getState() : null;
                $formModifier($event->getForm(), $state);
            }
        );

        $builder->get('tempState')->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) use ($formModifier) {
                $state = $event->getForm()->getData();
                $formModifier($event->getForm()->getParent(), $state);
            }
        );
    }

    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('id')
            ->add('name')
            ->add('slug')
            ->add('prefix')
            ->add('address')
            ->add('zipCode')
            ->add('passcode')
            ->add('cityHallPhone')
            ->add('timezone')
            ->add('yearFounded')
            ->add('squareMiles')
            ->add('countFTE')
            ->add('hrDirectorFirstName')
            ->add('hrDirectorLastName')
            ->add('hrNamePrefix')
            ->add('hrNameSuffix')
            ->add('hrDirectorTitle')
            ->add('hrDirectorPhone')
            ->add('hrDirectorEmail')
            ->add('photo')
            ->add('isRegistered')
            ->add('profileType')
            ->add('isValidated')
            ->add('doesCityAllowChanges')
            ->add('allowsJobAnnouncements')
            ->add('counties')
            ->add('urls')
            ->add('censusPopulations')
            ->add('operationHours')
        ;
    }

//    protected function configureSideMenu(MenuItemInterface $menu, $action, AdminInterface $childAdmin = null)
//    {
//        if (!$childAdmin && !in_array($action, ['edit', 'show'])) {
//            return;
//        }
//
//        $admin = $this->isChild() ? $this->getParent() : $this;
//        $id = $admin->getRequest()->get('id');
//        $container = $this->getConfigurationPool()->getContainer();
//        $sonata_admin = $admin->getRequest()->get('_sonata_admin');
//        $child_sonata_admin = 'app.admin.city|app.admin.job_title';
//        $childId = $admin->getRequest()->get('childId');
//        $mainId = $id;
//
//        if ($this->isGranted('LIST')) {
//            $mainTitle = 'Manage Job Titles';
//            $title = '';
//            $mainRoute = 'admin_app_city_city_jobtitle_list';
//            $route = $mainRoute;
//            if ($sonata_admin == $child_sonata_admin) {
//                $title = 'Total Submitted Interest: ';
//                if ($childId != null) {
//                    $title .= (string)$this->getSubmittedJobTitleInterest($childId);
//                    $mainTitle = 'Manage Submitted Interest';
//                    $mainRoute = 'admin_app_user_jobseekeruser_submittedjobtitleinterest_list';
//                    $mainId = $childId;
//                } else {
//                    $title .=(string)$this->getSubmittedJobTitleInterest();
//                }
//            }
//
//            $menu->addChild($mainTitle, [
//                'uri' => $container->get('router')->generate($mainRoute, ['id' => $mainId])
//            ]);
//            $menu->addChild($title, [
//                'uri' => $container->get('router')->generate($route, ['id' => $id])
//            ]);
//
//        }
//    }

    /**
     * Get Job Title Submitted Interest count
     * @return int|mixed
     */
    private function getSubmittedJobTitleInterest($jobTitleId = null)
    {
        /* @var $object City */
        $city = $this->getSubject();

        /* @var $modelManager \Sonata\DoctrineORMAdminBundle\Model\ModelManager */
        $modelManager = $this->getModelManager();
        $entityManager = $modelManager->getEntityManager($this->getClass());
        /* @var $repository SubmittedJobTitleInterestRepository */
        $repository = $entityManager->getRepository(SubmittedJobTitleInterest::class);

        if ($jobTitleId) {
            return $repository->getSubmittedJobTitleInterestCountByJobTitle($jobTitleId);
        }

        if ($city) {
            return $repository->getSubmittedJobTitleInterestCountByCity($city);
        }

        return 0;
    }


    /**
     * @param City $object
     */
    public function prePersist($object)
    {
        $object->setInverseSide();

        $orderBy = 1;
        foreach ($object->getDepartments() as $department) {
            $department->setOrderByNumber($orderBy++);
        }

    }

    /**
     * @param City $object
     * @throws Exception
     */
    public function preUpdate($object)
    {
        $object->setInverseSide();

        if ($object->getIsSuspended() && (false == $object->getSuspensionEmailSentAt())) {
            $subject = 'Your CityGovJobs.com City Account Was Suspended';
            $template = 'emails/city_suspended.html.twig';
            $body = $this->getConfigurationPool()->getContainer()->get('templating')->render($template, array('city' => $object));
            $message = (new \Swift_Message($subject))
                ->setFrom('no-reply@citygovjobs.com')
                ->setTo($object->getAdminCityUser()->getEmail())
                ->setBody($body,'text/html')
            ;
            $mailer = $this->getConfigurationPool()->getContainer()->get('mailer');
            $mailer->send($message);
            $object->setSuspensionEmailSentAt(new \DateTime());
        } elseif (false == $object->getIsSuspended() && $object->getSuspensionEmailSentAt()) {
            $object->setSuspensionEmailSentAt(null);
        }

        $this->updateDepartmentOrder($object);
    }

    public function getStateFromCounty($queryBuilder, $alias, $field, $value)
    {
        if (!$value['value']) {
            return;
        }

        $queryBuilder->leftJoin($alias.'.counties', 'county');
        $queryBuilder->leftJoin('county.state', 'state');
        $queryBuilder->andWhere($queryBuilder->expr()->orX(
            $queryBuilder->expr()->like('state.name', $queryBuilder->expr()->literal('%' . $value['value'] . '%'))
        ));

        return true;
    }

    /**
     * @param City $city
     */
    private function updateDepartmentOrder($city)
    {
        /** @var DepartmentRepository $departmentRepo */
        $departmentRepo = $this->getModelManager()->getEntityManager($this->getClass())->getRepository(City\Department::class);

        $sortedDepts = $departmentRepo->getDepartmentOrderNumbers($city->getId());

        foreach ($city->getDepartments() as $department) {
            foreach ($sortedDepts as $dept) {
                if ($department->getId() == $dept['id']) {
                    $department->setName($dept['name']);
                    $department->setOrderByNumber($dept['order_by_number']);
                }
            }
        }


        $orderBy = 0;
        foreach ($city->getDepartments() as $department) {
            if ($department->getId()) {
                $orderBy = max($orderBy, $department->getOrderByNumber());
            }

            if ( ! $department->getId() && ! $department->getOrderByNumber()) {
                $department->setOrderByNumber(++$orderBy);
            }
        }
    }
}
