<?php

namespace App\Admin\City;

use App\Entity\City;
use App\Entity\City\Department;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelType;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\DoctrineORMAdminBundle\Filter\ModelAutocompleteFilter;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class DepartmentAdmin extends AbstractAdmin
{
    protected $parentAssociationMapping = 'city';

    protected $datagridValues = [
        '_sort_order' => 'ASC',
        '_sort_by' => 'name',
    ];

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('name', null, [
                'label' => 'Filter by Department',
                'show_filter' => true,
            ])
            ->add('city', ModelAutocompleteFilter::class, [
                'label' => 'Filter by City',
                'show_filter' => true
            ], null, [
                'property' => 'name',
                'to_string_callback' => function($entity) {
                    return $entity->getCityAndState();
                }
            ])
//            ->add('jobTitles', null, [
//                'label' => 'Filter by Job Title',
//                'show_filter' => true,
//            ])
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('name')
            ->add('city')
            ->add('jobTitles')
            ->add('divisions')
            ->add('_action', null, [
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
        $request = $this->getRequest()->getRequestUri();
        $isEdit = false;
        if ($this->getSubject() && $this->getSubject()->getID()) {
            $isEdit = true;
        }

        $city = null;
        /* @var $modelManager \Sonata\DoctrineORMAdminBundle\Model\ModelManager */
        $modelManager = $this->getModelManager();
        /* @var $departmentRepository \App\Repository\City\DepartmentRepository */
        $cityRepository = $modelManager->getEntityManager($this->getClass())->getRepository(City::class);

        if(((false !== strpos($request, 'city')) && (false !== strpos($request, 'list'))) ||
            (false !== strpos($request, 'city='))) {
            $cityId = explode('city=', $request)[1];
            if($cityId){
                $city = $cityRepository->findOneBy([
                    'id' => $cityId
                ]);
            }
        }

        /** @var Department $department */
        $department  = $this->getSubject();
        $currentCity = null;
        $routeName   = $this->getRequest()->attributes->get('_route');

        if ($routeName == 'admin_app_city_edit') {
            $cityId      = $this->getRequest()->attributes->get('id');
            $currentCity = $cityRepository->findOneBy([
                'id' => $cityId
            ]);
        }

        if ($currentCity && $department) {
            $ajaxUrl = '/city/' . $currentCity->getSlug() . '/update/' . $department->getId() . '/department';
        } else {
            $ajaxUrl = null;
        }

        $formMapper
            ->add('orderByNumber', HiddenType::class, [
                'label'  => 'Order On Profile',
                'attr'   => [
                    'class'              => 'city-department-row admin-city-department-row',
                    'data-department-id' => $department ? $department->getId() : null,
                    'data-order'         => $department ? $department->getOrderByNumber() : null,
                ]
            ])
            ->add('hideOnProfilePage', null, [
                'label' => 'Hide On Profile',
                'attr'  => [
                    'class'         => 'is-hide-department',
                    'data-ajax-url' => $ajaxUrl,
                ]
            ], [
                'custom_field_size' => 2
            ])
            ->add('name', null, [
                'label' => 'Name of Department'
            ], ['custom_field_size' => 8])
        ;
        if ((false !== strpos($request, 'department') and false !== strpos($request, 'create'))) {
            $formMapper
                ->add('city', ModelType::class, [
                    'data' => $city,
                    'btn_add' => false,
                    'label' => false,
                    'attr' => [
                        'class' => 'visibility-hidden'
                    ]
                ], ['custom_field_size' => 4]);
        }
        /*
        if (($isEdit and (false !== strpos($request, 'department')))) {
            $departmentId = $this->getSubject()->getID();
            $routeIds = [
                'parent_id' => $departmentId,
            ];

            $formMapper
                ->add('jobTitles', CollectionType::class, [
                    'by_reference' => false,
                    'type_options' => ['delete' => false],
                ], [
                    'edit' => 'inline',
                    'inline' => 'child_table',
                    'edit_child_admin_route' => 'admin_app_department_jobtitle_edit',
                    'delete_route' => 'admin_app_department_jobtitle_delete',
                    'routeIds' => $routeIds,
                    'childEditText' => 'View/Edit Details'
                ]);
        }
        */
    }

    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('name')
            ->add('city')
            ->add('jobTitles')
        ;
    }

    /**
     * @param Department $object
     */
    public function prePersist($object)
    {
        foreach ($object->getJobTitles() as $jobTitle) {
            /* @var $jobTitle \App\Entity\City\JobTitle */
            $jobTitle->setDepartment($object);

            $city = null;
            /* JobTitle has a one to many relationship to City */
            if($object->getCity()){
                $city = $object->getCity();
            } elseif ($jobTitle->getCity()) {
                $city = $jobTitle->getCity();
            }

            if($city){
                $jobTitle->setCity($city);
            }
        }

        foreach ($object->getDivisions() as $division) {
            $division->setDepartment($object);
        }
    }

    /**
     * @param Department $object
     */
    public function preUpdate($object)
    {
        foreach ($object->getJobTitles() as $jobTitle) {
            /* @var $jobTitle \App\Entity\City\JobTitle */
            $jobTitle->setDepartment($object);

            $city = null;
            /* JobTitle has a one to many relationship to City */
            if($object->getCity()){
                $city = $object->getCity();
            } elseif ($jobTitle->getCity()) {
                $city = $jobTitle->getCity();
            }

            if($city){
                $jobTitle->setCity($city);
            }
        }

        foreach ($object->getDivisions() as $division) {
            $division->setDepartment($object);
        }
    }
}
