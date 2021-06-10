<?php

namespace App\Admin\City;

use App\Entity\City;
use App\Entity\City\Department;
use App\Entity\City\JobTitle;
use App\Entity\JobTitle\Lookup\JobTitleName;
use App\Repository\City\CountyRepository;
use App\Repository\City\DepartmentRepository;
use App\Repository\City\StateRepository;
use App\Repository\CityRepository;
use App\Service\JobTitleML;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\AdminBundle\Form\Type\ModelType;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\CoreBundle\Form\Type\CollectionType;
use Sonata\DoctrineORMAdminBundle\Filter\ModelAutocompleteFilter;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;


class JobTitleAdmin extends AbstractAdmin
{

    /**
     * @var JobTitleML
     */
    private $jobTitleML;

    /**
     * JobTitleAdmin constructor.
     *
     * @param string $code
     * @param string $class
     * @param string $baseControllerName
     * @param JobTitleML $jobTitleML
     */
    public function __construct(string $code, string $class, string $baseControllerName, JobTitleML $jobTitleML)
    {
        parent::__construct($code, $class, $baseControllerName);

        $this->jobTitleML = $jobTitleML;
    }

    public function setPagerType($pagerType)
    {
        parent::setPagerType('simple'); // TODO: This is a workaround because I can't set 'pager_type: "simple"' to work in services.yaml
    }

    /**
     * @param RouteCollection $collection
     */
    protected function configureRoutes(RouteCollection $collection)
    {
        parent::configureRoutes($collection);
        $collection
            ->add('undelete', $this->getRouterIdParameter().'/undelete');
    }

    public function getParentAssociationMapping()
    {
        // use the mapping that has already been set
        if (!is_null($this->parentAssociationMapping)) {
            return $this->parentAssociationMapping;
        }

        // prevent calling getObject on a non-existent parent
        if (!($parent = $this->getParent())) {
            return;
        }

        // get class metadata for the current admin's subject
        $metadata = $this->getModelManager()->getMetadata(get_class($this->getNewInstance()));

        // check if this admin's subject has a field association for the given parent's subject
        if (!$metadata || !($associations = $metadata->getAssociationsByTargetClass(get_class($parent->getNewInstance())))) {
            return;
        }

        // use the first association as the field to access the parent object
        return $this->parentAssociationMapping = strtolower(key($associations));
    }

    protected $datagridValues = [
        '_sort_order' => 'ASC',
        '_sort_by' => 'name',
    ];

    public function createQuery($context = 'list')
    {
        $query = parent::createQuery($context);
        $em = $query->getQueryBuilder()->getEntityManager();
        $em->getFilters()->disable('softdeleteable');

        if ('list' === $context) {
            $rootAlias = $query->getRootAliases()[0];
            $parameters = $this->getFilterParameters();
            if ('submittedJobTitleInterestCount' === $parameters['_sort_by']) {
                $query
                    ->leftJoin($rootAlias . '.submittedJobTitleInterests', 'sjti')
                    ->groupBy($rootAlias . '.id')
                    ->orderBy('COUNT(sjti.id)', $parameters['_sort_order']);
            }
        }
        return $query;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $request = $this->getRequest()->getRequestUri();
        /* @var $modelManager \Sonata\DoctrineORMAdminBundle\Model\ModelManager */
        $modelManager = $this->getModelManager();
        /* @var $departmentRepository \App\Repository\DepartmentRepository */
        $departmentRepository = $modelManager->getEntityManager($this->getClass())->getRepository(Department::class);

        /* @var $jobTitleRepository \App\Repository\JobTitleRepository */
        $jobTitleRepository = $modelManager->getEntityManager($this->getClass())->getRepository(JobTitle::class);

        $cityId = null;
        $datagridValues = $this->getDatagrid()->getValues();
        if(isset($datagridValues['city'])){
            $cityId = $datagridValues['city']['value'];
        }

        $isCityRoot= ($this->getRootCode() == 'app.admin.city')
            || ($this->hasRequest() && $this->getRequest()->get('pcode') == 'app.admin.city')
            || ($this->isChild() && $this->getParent()->getBaseCodeRoute() == 'app.admin.city');

        $datagridMapper
            ->add('jobTitleName', ModelAutocompleteFilter::class, [
                'label' => 'Job Title Name',
                'show_filter' => true,
                ],
            null,
                [
                    'callback' => function ($admin, $property, $value) {
                        $qb = $admin->getDatagrid()->getQuery();
                        $qb
                            ->where($qb->getRootAlias() . ".name LIKE :search")
                            ->setParameter('search', '%'.$value.'%');
                    },
                    'property'           => 'name'
                ]
            )
            ->add('city.counties.state', null, [
                'label' => 'State',
                'show_filter' => true
            ], null, [
                'query_builder' => function (StateRepository $r) {
                    return $r->createQueryBuilder('s')
                        ->orderBy('s.name')
                        ;
                }
            ])
            ->add('city.counties', ModelAutocompleteFilter::class, [
                'label' => 'County',
                'show_filter' => true,
            ], null, [
                'property' => 'name',
                'to_string_callback' => function($entity) {
                    return $entity->getDisplayName();
                },
            ])
        ;
        if($isCityRoot) {
            $datagridMapper
                ->add('city', null, [
                    'label' => 'City',
                    'show_filter' => true,
                ], null, [
                    'attr' => [
                        'read_only' => true,
                        'disabled' => true
                    ]
                ]);
        } else {
            $datagridMapper
                ->add('city', ModelAutocompleteFilter::class, [
                    'label' => 'City',
                    'show_filter' => true
                ], null,
                [
                    'callback' => function ($admin, $property, $value) {

                        $qb = $admin->getDatagrid()->getQuery();

                        $qb
                            ->join($qb->getRootAlias() . '.counties', 'county')
                            ->join('county.state', 'state')
                            ->addOrderBy($qb->getRootAlias() .'.name', 'ASC')
                            ->addOrderBy('state.name', 'ASC')
                            ->where($qb->getRootAlias() . ".name LIKE :search")
                            ->setParameter('search', '%'.$value.'%');
                        ;

                    },
                    'attr' => [
                        'class' => 'ja-filter-city'
                    ],
                    'property'           => 'name',
                    'to_string_callback' => function ($entity) {
                        return $entity->getCityAndState();
                    }
                ]);
        }
        if (isset($datagridValues['city']) && $datagridValues['city']['value']) {
            $datagridMapper
                ->add('department', null, [
                    'label' => 'Department',
                    'show_filter' => true
                ], null, [
                    'query_builder' => function (DepartmentRepository $r) use (&$datagridValues) {
                        if (isset($datagridValues['city']) && $datagridValues['city']['value']) {
                            return $r->createQueryBuilder('d')
                                ->where('d.city = :city')
                                ->orderBy('d.name')
                                ->setParameter('city', $datagridValues['city']['value'])
                                ;
                        }
                        return $r->createQueryBuilder('d')
                            ->orderBy('d.name')
                            ;
                    }
                ]);
        }
        $datagridMapper
            ->add('level', null, [
                'label' => 'Job Level',
            ])
            ->add('category', null, [
                'label' => 'Job Category',
            ])
            ->add('type', null, [
                'label' => 'Job Type',
            ])
            ->add('isVacant')
            ->add('isHidden')
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('jobTitleName', null, [
                'label' => 'Job Title Name',
                'sortable' => true,
                'no_link' => true,
                'sort_field_mapping' => [
                    'fieldName' => 'name'
                ],
                'sort_parent_association_mappings' => [
                    ['fieldName' => 'jobTitleName']
                ]
            ])
            ->add('city')
            ->add('department')
            ->add('division')
            ->add('level')
            ->add('category')
            ->add('type')
            ->add('isVacant', null,
                [
                    'editable' => true
                ])
            ->add('isClosedPromotional', null,
                [
                    'editable' => true
                ])
            ->add('submittedJobTitleInterestCount', null, [
                'label' => 'Submitted Interest',
                'template' => 'admin/job_title/list_link_to_job_seeker.html.twig',
                'sortable' => true,
                'sort_field_mapping' => ['fieldName' => 'id'],
                'sort_parent_association_mappings' => [],
            ])
            ->add('deletedAt')
            ->add('_action', null, [
                'template' => 'admin/list/jobTitle_list_action.html.twig',
                'actions' => [
                    'show' => [],
                    'edit' => [],
                    'delete' => [],
                ],
            ])
        ;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $request    = $this->getRequest()->getRequestUri();
        $isEdit     = false;
        $cityId     = null;
        $department = null;

        $subject = $this->getSubject();
        if ($subject && $subject->getId()) {
            $isEdit = true;
            $city   = $subject->getCity();
            if ($city) {
                $cityId = $city->getId();
            }
            if ($subject->getDepartment() && $subject->getDepartment()->getId()) {
                $department = $subject->getDepartment();
            }
        }

        /* @var $modelManager \Sonata\DoctrineORMAdminBundle\Model\ModelManager */
        $modelManager = $this->getModelManager();
        /* @var $departmentRepository \App\Repository\City\DepartmentRepository */
        $departmentRepository = $modelManager->getEntityManager($this->getClass())->getRepository(Department::class);

        /* @var $divisionRepository \App\Repository\City\DivisionRepository */
        $divisionRepository = $modelManager->getEntityManager($this->getClass())->getRepository(City\Division::class);

        $formMapper
            ->with('Basic Info')
                ->add('jobTitleName', ModelAutocompleteType::class, [
                    'label'     => 'Job Title Name',
                    'btn_add'   => 'Add new',
                    'property'  => 'name',
                    'callback' => function ($admin, $property, $value) {
                        $qb = $admin->getDatagrid()->getQuery();
                        $alias = $qb->getRootAlias();

                        $qb->addOrderBy($alias . '.name', 'ASC')
                           ->andWhere($alias . '.name LIKE :search')
                           ->setParameter('search', '%' . $value . '%')
                        ;
                    },
                ], ['custom_field_size' => 4]);

        if (($isEdit and false === strpos($request, 'app.admin.department') and false !== strpos($request, 'jobtitle')) or (false !== strpos($request, 'jobtitle'))
            or (false !== strpos($request, 'job_title'))) {

            $cityAttr = array('data-city' => $cityId, 'class' => 'data-city job-title-admin-city');
            if ($isEdit) {
                $cityAttr['readonly'] = true;
            }

            $formMapper
                    ->add('city', ModelAutocompleteType::class, [
                        'property'    => 'name',
                        'placeholder' => '',
                        'required'    => true,
                        'label'       => 'City',
                        'btn_add'     => false,
                        'attr'        => $cityAttr,
                        'callback' => function ($admin, $property, $value) {
                            $qb = $admin->getDatagrid()->getQuery();
                            $qb->join($qb->getRootAlias() . '.counties', 'county')
                                ->join('county.state', 'state')
                                ->addOrderBy($qb->getRootAlias() . '.name', 'ASC')
                                ->addOrderBy('state.name', 'ASC')
                                ->where($qb->getRootAlias() . ".name LIKE :search")
                                ->setParameter('search', '%' . $value . '%');
                        },
                        'to_string_callback' => function ($entity) {
                            return $entity->getCityAndState();
                        },
                    ], ['custom_field_size' => 4])
                    ->add('department');

            if ($cityId && $department) {
                $formMapper
                    ->add('division', ModelType::class, [
                        'required' => false,
                        'query'    => $divisionRepository->getQueryBuilderByDepartmentAndCity($department, $cityId),
                        'attr'     => array(
                            'class' => 'job-title-division'
                        ),
                    ], ['custom_field_size' => 4]);
            }

            if ($isEdit) {
                $formMapper
                    ->add('level', ModelType::class, [
                        'required' => false,
                        'label'    => 'Job Level',
                    ], ['custom_field_size' => 4])
                    ->add('category', ModelType::class, [
                        'required' => false,
                        'multiple' => true,
                        'label'    => 'Job Category',
                    ], ['custom_field_size' => 4]);
            }

            $formMapper
                ->add('isClosedPromotional', null, [], [
                    'custom_field_size' => 4,
                ])
                ->add('type', ModelType::class, [
                    'required' => false,
                    'label'    => 'Job Type',
                ], ['custom_field_size' => 4])
            ->end();


            $builder      = $formMapper->getFormBuilder();
            $formModifier = function (FormInterface $form, City $jtCity = null) use ($departmentRepository, $divisionRepository, $department) {

                /* Only shows department if City is defined */
                $departments = [];
                if ($jtCity) {
                        $departments = $departmentRepository->findByCity($jtCity);
                }

                $form
                    ->add('department', EntityType::class, [
                        'auto_initialize' => false,
                        'placeholder' => '',
                        'class'       => Department::class,
                        'required'    => false,
                        'label'       => 'Department',
                        'choices'     => $departments,
                        'data'        => $department,
                        'attr'        => array(
                            'class'           => 'job-title-department data-department',
                            'data-department' => $department ? $department->getId() : null,
                        ),
                    ], ['custom_field_size' => 3, 'pre_set_field' => true]);
            };

            $builder->addEventListener(
                FormEvents::PRE_SET_DATA,
                function (FormEvent $event) use ($formModifier) {

                    $jobTitle   = $event->getData();
                    $city = $jobTitle && $jobTitle->getCity() ? $jobTitle->getCity() : null;
                    $formModifier($event->getForm(), $city);
                }
            );

            $builder->get('city')->addEventListener(
                FormEvents::POST_SUBMIT,
                function (FormEvent $event) use ($formModifier) {
                    $city = $event->getForm()->getData();
                    $formModifier($event->getForm()->getParent(), $city);
                }
            );
        }
    }

    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('id')
            ->add('jobTitleName', null, ['no_link' => true])
            ->add('city')
            ->add('department')
            ->add('division')
            ->add('positionCount')
            ->add('titleCount')
            ->add('level')
            ->add('category')
            ->add('type')
            ->add('monthlySalaryLow')
            ->add('monthlySalaryHigh')
            ->add('hourlyWageLow')
            ->add('hourlyWageHigh')
        ;
    }

    public function getExportFields()
    {
        $fields = [
            'cityName' => 'city',
            'stateName' => 'city.stateFromCounty',
            'countyName'=> 'city.firstCounty',
            'department'=> 'department',
            'division'=> 'division',
            'jobTitle' => 'jobTitleName.name',
            'jobType' => 'type',
            'level' => 'level',
            'jobCategory' => 'category',
            'positionCount' => 'positionCount',
            'monthlySal-low' => 'monthlySalaryLow',
            'monthlySal-high' => 'monthlySalaryHigh',
            'hrlyWage-low' => 'hourlyWageLow',
            'hrlyWage-high' => 'hourlyWageHigh',
            'dateUpdated' => 'updatedAt'
        ];

        foreach ($this->getExtensions() as $extension) {
            if (method_exists($extension, 'configureExportFields')) {
                $fields = $extension->configureExportFields($this, $fields);
            }
        }
        return $fields;
    }

    /**
     * @param JobTitle $object
     *
     * @throws \Phpml\Exception\FileException
     * @throws \Phpml\Exception\SerializeException
     */
    public function prePersist($object)
    {
        $this->doCommonPreLogic($object);

        // CIT-569: In Job Title Admin on Create... use Machine Learning to set Category & Level
        $this->jobTitleML->initializeJobTitle($object);
    }

    /**
     * @param JobTitle $object
     */
    public function preUpdate($object)
    {
        $this->doCommonPreLogic($object);
    }

    /**
     * @param JobTitle $object
     */
    private function doCommonPreLogic($object) {
        $city = $object->getCity();
        if ($city) {
            /* @var $department \App\Entity\City\Department */
            $department = $object->getDepartment();

            if ($department) {
                $department->setCity($city);
            }
        }

        $submittedJobTitleInterests = $object->getSubmittedJobTitleInterests();
        foreach ($submittedJobTitleInterests as $submittedJobTitleInterest) {
            $submittedJobTitleInterest->setJobTitle($object);
        }
    }

    public function getDepartmentChoices($cityId) {
        /* @var $modelManager \Sonata\DoctrineORMAdminBundle\Model\ModelManager */
        $modelManager = $this->getModelManager();
        /* @var $departmentRepository \App\Repository\City\DepartmentRepository */
        $departmentRepository = $modelManager->getEntityManager($this->getClass())->getRepository(Department::class);

        $departmentChoiceArray = [];
        $departmentChoiceArray['-- No Department --'] = 'no_department';
        if ($cityId) {
            $results = $departmentRepository->getQueryBuilderToFindByCity($cityId)->getQuery()->getResult();
            foreach($results as $result) {
                $departmentChoiceArray[$result->getName()] = $result->getId();
            }
        }

        return $departmentChoiceArray;
    }
}
