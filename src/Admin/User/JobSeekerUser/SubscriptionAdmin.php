<?php

namespace App\Admin\User\JobSeekerUser;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\DoctrineORMAdminBundle\Filter\ModelAutocompleteFilter;

final class SubscriptionAdmin extends AbstractAdmin
{

    public function configure()
    {
        parent::configure();
        $this->classnameLabel = 'Job Seeker Subscription';
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper): void
    {
        $datagridMapper
            ->add('jobSeekerUser', ModelAutocompleteFilter::class, [], null, [
                'property' => ['lastname', 'firstname']
            ])
            ->add('subscriptionPlan')
        ;
    }

    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper
            ->add('jobSeekerUser')
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
            ->add('jobSeekerUser')
            ->add('subscriptionPlan')
            ->add('expiresAt')
            ->add('cancelledAt')
        ;
    }

    protected function configureShowFields(ShowMapper $showMapper): void
    {
        $showMapper
            ->add('id')
            ->add('jobSeekerUser')
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
