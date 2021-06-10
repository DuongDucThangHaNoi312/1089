<?php

namespace App\Admin\City;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\DoctrineORMAdminBundle\Filter\ModelAutocompleteFilter;

final class SubscriptionAdmin extends AbstractAdmin
{

    public function configure()
    {
        parent::configure();
        $this->classnameLabel = 'City Subscription';
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper): void
    {
        $datagridMapper
            ->add('city', ModelAutocompleteFilter::class, [], null, [
                'property' => 'name',
                'to_string_callback' => function($entity) {
                    return $entity->getCityAndState();
                }
            ])
            ->add('subscriptionPlan')
        ;
    }

    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper
            ->add('city')
            ->add('subscriptionPlan')
            ->add('createdAt')
            ->add('expiresAt')
            ->add('cancelledAt')
            ->add('updatedAt')
            ->add('createdBy')
            ->add('updatedBy')
            ->add('_action', null, [
                'actions' => [
                    'show' => [],
                    'edit' => [],
                    'delete' => [],
                ],
            ])
        ;
    }

    protected function configureFormFields(FormMapper $formMapper): void
    {
        $formMapper
            ->add('city', ModelAutocompleteType::class, [
                'property' => 'name',
                'to_string_callback' => function($entity) {
                    return $entity->getCityAndState();
                },
            ])
            ->add('subscriptionPlan')
            ->add('expiresAt')
            ->add('cancelledAt')
        ;
    }

    protected function configureShowFields(ShowMapper $showMapper): void
    {
        $showMapper
            ->add('id')
            ->add('city')
            ->add('subscriptionPlan')
            ->add('expiresAt')
            ->add('cancelledAt')
            ->add('createdAt')
            ->add('updatedAt')
            ->add('createdBy')
            ->add('updatedBy')
        ;
    }
}
