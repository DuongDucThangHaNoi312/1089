<?php

declare(strict_types=1);

namespace App\Admin\City;

use App\Entity\City;
use App\Entity\City\Department;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelType;
use Sonata\AdminBundle\Show\ShowMapper;

final class DivisionAdmin extends AbstractAdmin
{

    protected function configureDatagridFilters(DatagridMapper $datagridMapper): void
    {
        $datagridMapper
            ->add('name')
            ->add('city')
            ->add('department')
            ;
    }

    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper
            ->add('name')
            ->add('city')
            ->add('department')
            ->add('_action', null, [
                'actions' => [
                    'show' => [],
                    'edit' => [],
                    'delete' => [],
                ],
            ]);
    }

    protected function configureFormFields(FormMapper $formMapper): void
    {
        $request = $this->getRequest()->getRequestUri();
        $city = null;
        $department = null;
        /* @var $modelManager \Sonata\DoctrineORMAdminBundle\Model\ModelManager */
        $modelManager = $this->getModelManager();
        /* @var $cityRepository \App\Repository\CityRepository */
        $cityRepository = $modelManager->getEntityManager($this->getClass())->getRepository(City::class);
        /* @var $departmentRepository \App\Repository\City\DepartmentRepository */
        $departmentRepository = $modelManager->getEntityManager($this->getClass())->getRepository(Department::class);

        if(((false !== strpos($request, 'city')) && (false !== strpos($request, 'list'))) ||
            ((false !== strpos($request, 'city=')) && (false !== strpos($request, 'department='))) ) {
            $cityId = explode('city=', $request)[1];
            if($cityId){
                $city = $cityRepository->findOneBy([
                    'id' => $cityId
                ]);
            }
            $departmentId = explode('department=', $request)[1];
            if($departmentId){
                $department = $departmentRepository->findOneBy([
                    'id' => $departmentId
                ]);
            }
        }


        $formMapper
            ->add('name', null, [
                'label' => 'Name of Division'
            ], ['custom_field_size' => 8])
        ;
        if ((false !== strpos($request, 'division') and false !== strpos($request, 'create'))) {
            $formMapper
                ->add('city', ModelType::class, [
                    'data' => $city,
                    'btn_add' => false,
                    'label' => false,
                    'attr' => [
                        'class' => 'visibility-hidden'
                    ]
                ], ['custom_field_size' => 4])
            ->add('department', ModelType::class, [
                'data' => $department,
                'btn_add' => false,
                'label' => false,
                'attr' => [
                        'class' => 'visibility-hidden'
                ]
            ], ['custom_field_size' => 4])
            ;
        }
    }

    protected function configureShowFields(ShowMapper $showMapper): void
    {
        $showMapper
            ->add('name')
            ->add('city')
            ->add('department')
            ;
    }
}
