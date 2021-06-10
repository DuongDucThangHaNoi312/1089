<?php

declare(strict_types=1);

namespace App\Admin;

use App\Entity\City;
use App\Entity\JobAnnouncement;
use App\Repository\City\JobTitleRepository;
use App\Repository\CityRepository;
use App\Repository\JobAnnouncementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\AdminBundle\Form\Type\ModelType;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\DoctrineORMAdminBundle\Datagrid\OrderByToSelectWalker;
use Sonata\DoctrineORMAdminBundle\Filter\ModelAutocompleteFilter;
use Sonata\Form\Type\DatePickerType;
use Sonata\Form\Type\DateTimePickerType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use App\Service\JobAnnouncementStatusDecider;


/**
 * Class JobAnnouncementAdmin
 * @package App\Admin
 */
final class JobAnnouncementAdmin extends AbstractAdmin
{
    private $statusDecider;
    protected $formOptions = [
        'validation_groups' => ['job_announcement_active_dates', 'job_announcement_application_deadline', 'job_announcement_wage_salary', 'job_announcement_details']
    ];

    public function __construct(string $code, string $class, string $baseControllerName, JobAnnouncementStatusDecider $statusDecider)
    {
        parent::__construct($code, $class, $baseControllerName);

        $this->statusDecider = $statusDecider;
    }

    protected function configureRoutes(RouteCollection $collection) {
        parent::configureRoutes($collection);

        $collection->add('applicationUrl', $this->getRouterIdParameter() . '/application_url');
    }

    public function setPagerType($pagerType)
    {
        parent::setPagerType('simple'); // TODO: This is a workaround because I can't set 'pager_type: "simple"' to work in services.yaml
    }

    public function createQuery($context = 'list')
    {
        $query = parent::createQuery($context);

        if ($context == 'list') {
            $request    = $this->getRequest();
            $sortFilter = $request->query->get('filter');
            if (isset($sortFilter['_sort_order']) && isset($sortFilter['_sort_by']) && $sortFilter['_sort_by'] == 'getCountOfViews') {
                $query->leftJoin($query->getRootAliases()[0] . '.views', 'jav')
                      ->groupBy($query->getRootAliases()[0] . '.id')
                      ->orderBy('COUNT(jav.id)', $sortFilter['_sort_order'])
                      ->setMaxResults(1)
                ;

                $params = $this->getFilterParameters();
                if (key_exists('viewFrom', $params) && $params['viewFrom']['value']) {
                    $from = new \DateTime($params['viewFrom']['value']);
                    $query->andWhere('jav.createdAt >= :vfdate')
                        ->setParameter('vfdate', $from->format('Y-m-d'));
                }
                if (key_exists('viewTo', $params) && $params['viewTo']['value']) {
                    $to = new \DateTime($params['viewTo']['value']);
                    $query->andWhere('jav.createdAt <= :vtdate')
                          ->setParameter('vtdate', $to->format('Y-m-d'));
                }
            }

            $params = $this->getFilterParameters();

            if ((key_exists('viewFrom', $params) && $params['viewFrom']['value'])
                || (key_exists('viewTo', $params) && $params['viewTo']['value'])
                || (isset($sortFilter['_sort_order']) && isset($sortFilter['_sort_by']) && $sortFilter['_sort_by'] == 'getCountOfImpression')) {
                $query->leftJoin($query->getRootAliases()[0] . '.jobAnnouncementImpressions', 'jai');
            }
            if (key_exists('viewFrom', $params) && $params['viewFrom']['value']) {
                $from = new \DateTime($params['viewFrom']['value']);

                $query->andWhere('jai.createdAt >= :vfdate')
                      ->setParameter('vfdate', $from->format('Y-m-d'));
            }
            if (key_exists('viewTo', $params) && $params['viewTo']['value']) {
                $to = new \DateTime($params['viewTo']['value']);
                $query->andWhere('jai.createdAt <= :vtdate')
                      ->setParameter('vtdate', $to->format('Y-m-d'));
            }

            if (isset($sortFilter['_sort_order']) && isset($sortFilter['_sort_by']) && $sortFilter['_sort_by'] == 'getCountOfImpression') {
                $query->groupBy($query->getRootAliases()[0] . '.id')
                      ->orderBy('COUNT(jai.id)', $sortFilter['_sort_order'])
                      ->setMaxResults(1);
            }
        }

        return $query;
    }

    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper): void
    {
        $datagridMapper
            ->add('jobTitle.jobTitleName', ModelAutocompleteFilter::class, [
                'label' => 'Job Title',
            ],
                null,
                [
                    'callback' => function ($admin, $property, $value) {
                        $qb = $admin->getDatagrid()->getQuery();
                        $alias = $qb->getRootAlias();
                        $qb->where($alias . '.name LIKE :search')
                           ->setParameter('search', '%'.$value.'%');
                    },
                    'property'           => 'name'
                ]
            )
            ->add('jobTitle.category', null, [
                'label' => 'Job Category'
            ])
            ->add('wageSalaryUnit')
            ->add('status')
            ->add('assignedTo')
            ->add('jobTitle.city', ModelAutocompleteFilter::class, [
                    'show_filter' => true,
                    'label' => 'City'
                ],
                null,
                [
                    'callback' => function ($admin, $property, $value) {
                        $qb = $admin->getDatagrid()->getQuery();
                        $qb
                            ->join($qb->getRootAlias() . '.counties', 'county')
                            ->join('county.state', 'state')
                            ->orderBy('state.name', 'ASC')
                            ->where($qb->getRootAlias() . ".name LIKE :search")
                            ->setParameter('search', '%'.$value.'%');
                    },
                    'attr' => [
                        'class' => 'ja-filter-city'
                    ],
                    'property'           => 'name',
                    'to_string_callback' => function ($entity) {
                        return $entity->getCityAndState();
                    }
                ]
            )
            ->add('jobTitle.city.counties', ModelAutocompleteFilter::class, [
                'show_filter' => true,
                'label' => 'County'
            ],
                null,
                [
                    'property'           => 'name',
                    'to_string_callback' => function ($entity) {
                        return $entity->getDisplayName();
                    }
                ]
            )
            ->add('jobTitle.department', null, [
                'label' => 'Department',
                'show_filter' => true,
            ], null, [
                'attr' => [
                    'class' => 'ja-filter-department'
                ]
            ])
            ->add('viewFrom', 'doctrine_orm_callback', array(
                'label' => 'View From Date',
                'callback' => function($queryBuilder, $alias, $field, $value) {
                    if (!$value) {
                        return;
                    }

                    $queryBuilder->join($alias . '.views', 'view_date');
                    $queryBuilder->andWhere('view_date.createdAt >= :vfdate');
                    $queryBuilder->setParameter('vfdate', $value['value']->format('Y-m-d'));

                    return true;
                },
                'field_type' => DatePickerType::class
            ))
            ->add('viewTo', 'doctrine_orm_callback', array(
                'label' => 'View To Date',
                'callback' => function($queryBuilder, $alias, $field, $value) {
                    if (!$value) {
                        return;
                    }

                    if ( ! in_array('view_date', $queryBuilder->getAllAliases())) {
                        $queryBuilder->join($alias . '.views', 'view_date');
                    }
                    $queryBuilder->andWhere('view_date.createdAt <= :vtdate');
                    $queryBuilder->setParameter('vtdate', $value['value']->format('Y-m-d'));

                    return true;
                },
                'field_type' => DatePickerType::class
            ))
            ->add('postFrom', 'doctrine_orm_callback', [
                'label'      => 'Posted On Date From',
                'field_type' => DatePickerType::class,
                'callback'   => function ($queryBuilder, $alias, $field, $value) {
                    if ( ! $value) {
                        return;
                    }

                    $queryBuilder->andWhere($alias . '.startsOn >= :postFrom');
                    $queryBuilder->setParameter('postFrom', $value['value']->format('Y-m-d'));

                    return true;
                },
            ])
            ->add('postTo', 'doctrine_orm_callback', [
                'label'      => 'Posted On Date To',
                'field_type' => DatePickerType::class,
                'callback'   => function ($queryBuilder, $alias, $field, $value) {
                    if ( ! $value) {
                        return;
                    }

                    $queryBuilder->andWhere($alias . '.startsOn <= :postTo');
                    $queryBuilder->setParameter('postTo', $value['value']->format('Y-m-d'));

                    return true;
                },

            ])
            ->add('endedForm', 'doctrine_orm_callback', [
                'label'      => 'Ended On Date From',
                'field_type' => DatePickerType::class,
                'model_timezone' => 'UTC',
                'view_timezone' => 'America/Los_Angeles',
                'callback'   => function ($queryBuilder, $alias, $field, $value) {
                    if ( ! $value) {
                        return;
                    }

                    $queryBuilder->andWhere($alias . '.endsOn >= :endedForm');
                    $queryBuilder->setParameter('endedForm', $value['value']->format('Y-m-d'));

                    return true;
                },
            ])
            ->add('endedTo', 'doctrine_orm_callback', [
                'label'      => 'Ended On Date To',
                'field_type' => DatePickerType::class,
                'model_timezone' => 'UTC',
                'view_timezone' => 'America/Los_Angeles',
                'callback'   => function ($queryBuilder, $alias, $field, $value) {
                    if ( ! $value) {
                        return;
                    }

                    $queryBuilder->andWhere($alias . '.endsOn <= :endedTo');
                    $queryBuilder->setParameter('endedTo', $value['value']->format('Y-m-d'));

                    return true;
                },
            ])
            ->add('isPostedByCGJ');
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper
            ->add('jobTitle.city.state', null, [
                'label' => 'State'
            ])
            ->add('jobTitle.city.counties', null, [
                'label' => 'County'
            ])
            ->add('jobTitleCity', null, [
                'label' => 'City Name'
            ])
            ->add('jobTitle', null, [
                'label' => 'Job Title',
                'associated_property' => 'name',
                'sortable' => true,
                'sort_field_mapping' => array('fieldName'=>'name'),
                'sort_parent_association_mappings' => array(
                    array('fieldName'=>'jobTitle'),
                    array('fieldName'=>'jobTitleName')
                ),
            ])
            ->add('jobTitle.type', null, [
                'label' => 'Type'
            ])
            ->add('jobTitle.department', null, [
                'label' => 'Dept'
            ])
            ->add('jobTitle.level', null, [
                'label' => 'Level'
            ])
            ->add('status', null, [
                'label' => 'Status'
            ])

            ->add('getCountOfViews', 'string', [
                'label'                            => 'Views',
                'sortable'                         => true,
                'sort_field_mapping'               => ['fieldName' => 'id'],
                'sort_parent_association_mappings' => [['fieldName' => 'views']],
                'template' => 'admin/job_announcement/view_count_field.html.twig'
            ])
            ->add('getCountOfImpression', null, [
                'label'                            => 'Impression',
                'sortable'                         => true,
                'sort_field_mapping'               => ['fieldName' => 'id'],
                'sort_parent_association_mappings' => [['fieldName' => 'jobAnnouncementImpressions']],
                'template' => 'admin/job_announcement/impression_count_field.html.twig'
            ])
            ->add('startsOn', null, [
                'label' => 'Posted On'
            ])
            ->add('endsOn', null, [
                'label' => 'Ends On'
            ])
            ->add('applicationDeadline', null, [
                'label' => 'Application Deadline'
            ])
            ->add('testUrl', null, [
                'template' => 'admin/list_application_url_test_action.html.twig'
            ])
            ->add('lastTestedDate')
            ->add('hasNoEndDate', null, [
                'label' => 'Is Continuous'
            ])
            ->add('isPostedByCGJ', null, [
                'label' => 'Posted By (admin)'
            ])
            ->add('_action', null, [
                'actions' => [
                    'show'   => [],
                    'edit'   => [],
                    'delete' => [],
                ],
            ]);
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper): void
    {

        /* CIT-548: make sure job title city is set */
        $subject = $this->getSubject();
        $total   = null;
        if ($subject instanceof JobAnnouncement && $subject->getId()) {
            if ( ! $subject->getJobTitleCity() && $subject->getJobTitle()) {
                $jobTitleCity = $subject->getJobTitle()->getCity();
                $subject->setJobTitleCity($jobTitleCity);
                $em = $this->getModelManager()->getEntityManager($this->getClass());
                $em->persist($jobTitleCity);
                $em->flush();
            }

            if ($subject->getJobTitle()) {
                $total = $subject->getJobTitle()->getSubmittedJobTitleInterestCount();
            }
        }


        $formMapper
            ->with('Job Title')
            ->add('jobTitleCity', ModelAutocompleteType::class, [
                'property'           => 'name',
                'placeholder'        => '',
                'required'           => true,
                'label'              => 'City Name',
                'btn_add'            => false,
                'attr'               => [
                    'class' => 'ja-job-title-city',
                ],
                'callback' => function ($admin, $property, $value) {
                    $datagrid = $admin->getDatagrid();
                    /** @var QueryBuilder $queryBuilder */
                    $queryBuilder = $datagrid->getQuery();
                    $alias = $queryBuilder->getRootAlias();
                    $queryBuilder
                        ->join($alias . '.counties', 'counties')
                        ->andWhere('counties.isActive = 1')
                        ->andWhere($alias . '.prefix IS NOT NULL')
                        ->andWhere($alias . '.name LIKE :term')
                        ->setParameter('term', '%' . $value . '%')
                        ->orderBy($alias . '.name');
                },
                'to_string_callback' => function ($entity) {
                    return $entity->getCityAndState();
                },
            ], ['custom_field_size' => 4])
            ->add('jobTitle')
            ->add('submittedInterest',TextType::class, [
                'mapped'=>false,
                'required' => false,
                'data' => $total,
                'attr'           => [
                    'class' => 'job-submitted-interest',
                    'read_only' => true,
                    'disabled'  => true,
                ]
            ], ['custom_field_size' => 3])
            ->end()
            ->with('Job Alert / Announcement Dates')
            ->add('startsOn', DateTimePickerType::class, [
                'widget' => 'single_text',
                'model_timezone' => 'UTC',
                'view_timezone' => 'America/Los_Angeles',
                'format' => 'MM/dd/yyyy hh:mm a',
                'attr'   => [
                    'class' => 'ja-starts-on'
                ]
            ], ['custom_field_size' => 3])
            ->add('endsOn', DateTimePickerType::class, [
                'widget' => 'single_text',
                'model_timezone' => 'UTC',
                'view_timezone' => 'America/Los_Angeles',
                'format' => 'MM/dd/yyyy hh:mm a',
                'attr'   => [
                    'class' => 'ja-ends-on'
                ]
            ], ['custom_field_size' => 3])
            ->add('endDateDescription', null, [
                'attr' => [
                    'class' => 'ja-end-dates-description',
                ]
            ], ['custom_field_size' => 3])
            ->add('hasNoEndDate', null, [
                'attr' => array(
                    'class' => 'ja-has-no-end-date',
                )
            ], ['custom_field_size' => 3])
            ->add('applicationDeadline', DateTimePickerType::class, [
                'help' => 'Application Deadline is ignored when Job Alert/Announcement Has no End Date',
                'model_timezone' => 'UTC',
                'view_timezone' => 'America/Los_Angeles',
                'widget' => 'single_text',
                'format' => 'MM/dd/yyyy hh:mm a'
            ], ['custom_field_size' => 3])
        ;

        if ($subject instanceof JobAnnouncement && $subject->getId()) {
            $formMapper
                ->add('status', null, [
                    'attr' => [
                        'class' => 'readonly-select2',
                        'required' => false
                    ]
                ], ['custom_field_size' => 4]);
        }
        $formMapper
            ->end()
            ->with('Salary')
            ->add('wageSalaryLow', MoneyType::class, [
                'required' => false,
                'currency' => 'USD',
                'grouping' => true,
            ], ['custom_field_size' => 2])
            ->add('wageSalaryHigh', MoneyType::class, [
                'required' => false,
                'currency' => 'USD',
                'grouping' => true,
            ], ['custom_field_size' => 2])
            ->add('wageSalaryUnit', null, [], ['custom_field_size' => 2])
            ->add('wageRangeDependsOnQualifications')
            ->end()
            ->with('Description', ['description' => 'A Job Alert only needs a Job Announcement URL and Is Alert checked to true, a Job Announcement needs the Application URL and a Description'])
            ->add('applicationUrl', null, [
                'label' => 'Application or Job Announcement URL',
                'required' => true,
            ], ['custom_field_size' => 4])
            ->add('assignedTo', ModelAutocompleteType::class, [
                'required'           => false,
                'property'           => ['firstname', 'lastname', 'email'],
                'to_string_callback' => function ($entity) {
                    return $entity->getFirstname() . ' ' . $entity->getLastname() . ' - ' . $entity->getEmail();
                },
            ], ['custom_field_size' => 4])
            ->add('description', CKEditorType::class, ['required' => false], ['custom_field_size' => 8])
            ->add('isAlert')
            ->end()
            ->with('Location')
            ->add('street', null, [
                'attr'               => [
                    'class' => 'ja-location-street',
                ]
            ], ['custom_field_size' => 8])
            ->add('city', ModelAutocompleteType::class, [
                'attr'               => [
                    'class' => 'ja-location-city',
                ],
                'required'           => false,
                'label'              => 'City, State',
                'property'           => 'name',
                'to_string_callback' => function ($entity) {
                    return $entity->getCityAndState();
                },
            ], ['custom_field_size' => 5])
            ->add('zipcode', null, [
                'attr'               => [
                    'class' => 'ja-location-zipcode',
                ],
                'label' => 'Zip Code'
            ], ['custom_field_size' => 3])
        ->end();

        /** @var JobTitleRepository $jobTitleRepo */
        $jobTitleRepo = $this->getModelManager()->getEntityManager($this->getClass())->getRepository(City\JobTitle::class);

        $builder      = $formMapper->getFormBuilder();
        $formModifier = function (FormInterface $form, City $jtCity = null) use ($jobTitleRepo) {
            $jobTitles = [];
            if ($jtCity) {
                $jobTitles = $jobTitleRepo->findByCities([$jtCity]);
            }

            $form->add('jobTitle', EntityType::class, [
                'auto_initialize' => false,
                'label'           => 'Job Title',
                'class'           => City\JobTitle::class,
                'placeholder'     => '',
                'choices'         => $jobTitles,
                'choice_label'    => function ($entity) {
                    return (string) $entity->getName() . ' (' . $entity->getDepartment() . ' - ' . $entity->getType() . ')';
                },
                'attr'            => [
                    'class' => 'ja-job-titles'
                ]
            ]);
        };

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($formModifier) {
                /** @var JobAnnouncement $ja */
                $ja   = $event->getData();
                $city = $ja && $ja->getJobTitleCity() ? $ja->getJobTitleCity() : null;
                $formModifier($event->getForm(), $city);
            }
        );

        $builder->get('jobTitleCity')->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) use ($formModifier) {
                $city = $event->getForm()->getData();
                $formModifier($event->getForm()->getParent(), $city);
            }
        );

    }

    /**
     * @param ShowMapper $showMapper
     */
    protected function configureShowFields(ShowMapper $showMapper): void
    {
        $showMapper
            ->with('Job Title')
            ->add('jobTitle')
            ->end()
            ->with('Job Alert / Announcement Dates')
            ->add('startsOn', null, [
                'timezone' => 'America/Los_Angeles',
                'format' => 'M d, y h:m a T',
            ])
            ->add('endsOn', null, [
                'timezone' => 'America/Los_Angeles',
                'format' => 'M d, y hh:mm a T',
            ])
            ->add('hasNoEndDate')
            ->add('applicationDeadline', null, [
                'timezone' => 'America/Los_Angeles',
                'format' => 'M d, y h:m a T',
            ])
            ->add('status')
            ->end()
            ->with('Salary')
            ->add('wageSalaryUnit')
            ->add('wageSalaryLow')
            ->add('wageSalaryHigh')
            ->add('wageRangeDependsOnQualifications')
            ->end()
            ->with('Description')
            ->add('assignedTo')
            ->add('applicationUrl',null,[
                'label' => 'Application or Job Announcement'
            ])
            ->add('isAlert')
            ->add('description')
            ->end()
            ->with('Location')
            ->add('street')
            ->add('state')
            ->add('city')
            ->add('zipcode')
            ->end()
        ;
    }

    /**
     * @param JobAnnouncement $object
     *
     * @throws \Doctrine\ORM\ORMException
     */
    public function prePersist($object)
    {
        parent::prePersist($object);

        $this->doCommonPreLogic($object);

        $object->setIsPostedByCGJ(true);
        $status = $object->getStatus();
        if ($status && $status->getId() != JobAnnouncement::STATUS_ENDED && $status->getId() != JobAnnouncement::STATUS_ARCHIVED) {
            $object->getJobTitle()->setIsVacant(true);
        }
    }

    /**
     * @param JobAnnouncement $object
     *
     * @throws \Doctrine\ORM\ORMException
     */
    public function preUpdate($object)
    {
        parent::preUpdate($object);

        $this->doCommonPreLogic($object);
    }


    /**
     * @param JobAnnouncement $object
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function postUpdate($object)
    {
        parent::postUpdate($object);

        $this->updateJobTitleVacant($object);
    }

    /**
     * @param JobAnnouncement $object
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function postRemove($object)
    {
        parent::postRemove($object);

        $this->updateJobTitleVacant($object);
    }

    /**
     * @param JobAnnouncement $object
     *
     * @throws \Doctrine\ORM\ORMException
     */
    private function doCommonPreLogic($object)
    {
        if ($object->getCity()) {
            $object->setState($object->getCity()->getState());
        }

        $object->setStatus($this->statusDecider->decide($object));
        $status = $object->getStatus();
        if ($status && $status->getId() != JobAnnouncement::STATUS_ACTIVE && $status->getId() != JobAnnouncement::STATUS_ARCHIVED) {
            $object->getJobTitle()->setIsVacant(true);
        }
    }

    /**
     * CIT-509: In Admin, when Job Announcement is deleted or status is set in (ENDED, ARCHIVED), set related JobTitle.isVacant = false.
     *
     * @param JobAnnouncement $object
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    private function updateJobTitleVacant($object)
    {
        $jobTitle = $object->getJobTitle();
        $em       = $this->getModelManager()->getEntityManager($this->getClass());

        /* @var $jaRepository JobAnnouncementRepository */
        $jaRepository = $em->getRepository(JobAnnouncement::class);
        $isVacant     = $jaRepository->isJobTitleVacant($jobTitle->getId());

        if ($jobTitle->getIsVacant() != $isVacant) {
            $jobTitle->setIsVacant($isVacant);
            $em->flush();
        }
    }


    public function getExportFields()
    {
        $fields = [
            'State'                => 'jobTitle.city.state',
            'County'               => 'jobTitle.city.firstCounty',
            'City Name'            => 'jobTitleCity',
            'Job Title'            => 'jobTitle',
            'Type'                 => 'jobTitle.type',
            'Department'           => 'jobTitle.department',
            'Level'                => 'jobTitle.level',
            'Submitted Interest'   => 'jobTitle.countOfSubmittedInterest',
            'Status'               => 'status',
            'Posted On'            => 'startsOn',
            'Ended On'             => 'endsOn',
            'Application Deadline' => 'applicationDeadline',
            'Is Continuous'        => 'hasNoEndDate',
            'Posted By(Admin)'     => 'isPostedByCGJ',
            'Job Announcement URL'  =>'jobAnnouncementURL'
        ];

        return $fields;
    }


    public function getTemplate($name)
    {
        switch ($name) {
            case 'list':
                return 'admin/list/job_announcement_list.html.twig';
                break;
            default:
                return parent::getTemplate($name);
                break;
        }
    }

    public function getTotalViews()
    {
        $query = $this->getDatagrid()->getQuery();
        $query->select('DISTINCT '.current($query->getRootAliases()) . '.id');
        if ($query instanceof ProxyQueryInterface) {
            $sortBy = $query->getSortBy();

            if (!empty($sortBy)) {
                $query->addOrderBy($sortBy, $query->getSortOrder());
                $query = $query->getQuery();
                $query->setHint(Query::HINT_CUSTOM_TREE_WALKERS, [OrderByToSelectWalker::class]);
            } else {
                $query = $query->getQuery();
            }
        }

        /** @var EntityManagerInterface $em */
        $em = $this->getModelManager()->getEntityManager($this->getClass());
        $qb = $em->createQueryBuilder();

        $qb->select('COUNT(v.id)')
            ->from(JobAnnouncement\View::class, 'v')
            ->where($qb->expr()->in('v.jobAnnouncement', $query->getDQL()))
            ;

        foreach ($query->getParameters() as $p) {
            $qb->setParameter($p->getName(), $p->getValue(), $p->getType());

            /** View To & View From filter */

            if ($p->getName() == 'vfdate') {
                $qb->andWhere('v.createdAt >= :vfdate');
            }
            if ($p->getName() == 'vtdate') {
                $qb->andWhere('v.createdAt <= :vtdate');
            }
        }

        return $qb->getQuery()->getSingleScalarResult();
    }

    public function getJobAnnouncementViewCount(JobAnnouncement $ja)
    {
        /** @var EntityManagerInterface $em */
        $em = $this->getModelManager()->getEntityManager($this->getClass());
        $viewRepo = $em->getRepository(JobAnnouncement\View::class);

        $query = $this->getDatagrid()->getQuery();
        $from = $to = null;
        foreach ($query->getParameters() as $p) {
            if ($p->getName() == 'vfdate') {
                $from = $p->getValue();
            }
            if ($p->getName() == 'vtdate') {
                $to = $p->getValue();
            }
        }

        return $viewRepo->getJobAnnouncementViewInPeriod($ja->getId(), $from, $to);
    }

    public function getJobAnnouncementImpressionCount(JobAnnouncement $ja)
    {
        /** @var EntityManagerInterface $em */
        $em             = $this->getModelManager()->getEntityManager($this->getClass());
        $impressionRepo = $em->getRepository(JobAnnouncement\JobAnnouncementImpression::class);
        $query          = $this->getDatagrid()->getQuery();
        $from           = $to = null;
        foreach ($query->getParameters() as $p) {
            if ($p->getName() == 'vfdate') {
                $from = $p->getValue();
            }
            if ($p->getName() == 'vtdate') {
                $to = $p->getValue();
            }
        }

        return $impressionRepo->getJobAnnouncementImpressionInPeriod($ja->getId(), $from, $to);
    }
}
