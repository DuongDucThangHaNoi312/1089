<?php

namespace App\Admin\City;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\AdminBundle\Form\Type\ModelType;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\CoreBundle\Form\Type\CollectionType;
use Sonata\DoctrineORMAdminBundle\Filter\ModelAutocompleteFilter;

class CountyAdmin extends AbstractAdmin
{
    protected $datagridValues = [
        '_sort_order' => 'ASC',
        '_sort_by' => 'name',
    ];

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('name', null, [
                'label' => 'Filter by County Name',
                'show_filter' => true,
            ])
            ->add('cities', ModelAutocompleteFilter::class, [
                'label' => 'Filter by City',
                'show_filter' => true,
            ], null, [
                'property' => 'name',
                'to_string_callback' => function($entity) {
                    return $entity->getCityAndState();
                },
            ])
            ->add('state', null, [
                'label' => 'Filter by State',
                'show_filter' => true,
            ])
            ->add('isActive', null, [
                'label' => 'Filter by Active',
                'show_filter' => true,
            ])
            ->add('activateForCitySearch', null, [
                'label' => 'Filter by Active For City Search',
                'show_filter' => true,
            ])
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('state')
            ->add('name', null, [
                'label' => 'County Name',
            ])
            ->add('slug')
            ->add('cities')
            ->add('isActive', null, ['editable' => true])
            ->add('activateForCitySearch', null, ['editable' => true])
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

        $formMapper
            ->add('name');

        if (false !== strpos($request, 'county')) {
            $formMapper
                ->add('cities', ModelAutocompleteType::class, [
                    'by_reference' => false,
                    'multiple'     => true,
                    'property'     => 'name'
                ])
                ->add('state');
        }

        $formMapper
            ->add('isActive')
            ->add('activateForCitySearch');

    }

    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('id')
            ->add('name')
            ->add('cities')
            ->add('state')
            ->add('isActive')
            ->add('activateForCitySearch')
        ;
    }
}
