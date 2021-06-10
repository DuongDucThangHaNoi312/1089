<?php

namespace App\Admin\User\JobSeekerUser;

use App\Entity\User\JobSeekerUser\SubmittedJobTitleInterest;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelType;
use Sonata\AdminBundle\Show\ShowMapper;

class SubmittedJobTitleInterestAdmin extends AbstractAdmin
{
    protected $parentAssociationMapping = 'jobTitle';

    protected $datagridValues = [
        '_sort_order' => 'ASC',
        '_sort_by' => 'jobTitle',
    ];


    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('jobTitle')
            ->add('jobSeekerUser')
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('jobTitle')
            ->add('jobSeekerUser')
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
            ->add('jobTitle', ModelType::class)
            ->add('jobSeekerUser', ModelType::class)
        ;
    }

    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('jobTitle')
            ->add('jobSeekerUser')
        ;
    }
}
