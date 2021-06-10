<?php

namespace App\Admin\City;

use App\Entity\City\State;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\CoreBundle\Form\Type\CollectionType;
use Sonata\DoctrineORMAdminBundle\Filter\ModelAutocompleteFilter;

class StateAdmin extends AbstractAdmin
{
    protected $datagridValues = [
        '_sort_order' => 'ASC',
        '_sort_by' => 'name',
    ];

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('id')
            ->add('name')
            ->add('isActive')
            ->add('activatedDate')
            ->add('counties', ModelAutocompleteFilter::class, [
                'label' => 'Filter by County',
                'show_filter' => true,
            ], null, [
                'property' => 'name',
                'to_string_callback' => function($entity) {
                    return $entity->getDisplayName();
                },
            ])
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('id')
            ->add('name')
            ->add('slug')
            ->add('isActive', null, array('editable' => true))
            ->add('activatedDate')
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
        $formMapper
//            ->add('id')
            ->add('name')
            ->add('isActive')
            ->add('activatedDate')
            ->add('counties', ModelAutocompleteType::class, [
                'by_reference' => false,
                'multiple' => true,
                'property' => 'name'
            ])
        ;
    }

    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('id')
            ->add('name')
            ->add('isActive')
            ->add('activatedDate')
            ->add('counties')
        ;
    }

    /**
     * @param State $object
     */
    public function prePersist($object)
    {
        foreach ($object->getCounties() as $county) {
            /* @var $county \App\Entity\City\County */
            $county->setState($object);
        }

    }

    /**
     * @param State $object
     */
    public function preUpdate($object)
    {
        foreach ($object->getCounties() as $county) {
            /* @var $county \App\Entity\City\County */
            $county->setState($object);
        }
    }
}
