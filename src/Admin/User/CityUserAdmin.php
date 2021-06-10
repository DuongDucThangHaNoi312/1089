<?php

namespace App\Admin\User;

use App\Admin\UserAdmin as BaseUserAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\DoctrineORMAdminBundle\Filter\ModelAutocompleteFilter;

class CityUserAdmin extends BaseUserAdmin
{
    protected function configureDatagridFilters(DatagridMapper $filterMapper): void
    {
        parent::configureDatagridFilters($filterMapper);
        $filterMapper
            ->add('firstname')
            ->add('lastname')
            ->add('city', ModelAutocompleteFilter::class, [], null, [
                'property' => 'name',
                'to_string_callback' => function($entity) {
                    return $entity->getCityAndState();
                }
            ])
        ;
    }

    protected function configureListFields(ListMapper $listMapper): void
    {
        parent::configureListFields($listMapper);
    }

    protected function configureFormFields(FormMapper $formMapper): void
    {
        parent::configureFormFields($formMapper);
        $formMapper
            ->tab('User')
                ->with('Profile', ['class' => 'col-md-6'])
                    ->add('city', ModelAutocompleteType::class, [
                        'property' => 'name',
                        'to_string_callback' => function($entity) {
                            return $entity->getCityAndState();
                        },
                    ])
                    ->add('jobTitle')
                    ->add('department')
                ->end()
            ->end()
        ;
    }

    protected function configureShowFields(ShowMapper $showMapper): void
    {
        parent::configureShowFields($showMapper);
        $showMapper
            ->add('jobTitle')
            ->add('department')
        ;
    }
}
