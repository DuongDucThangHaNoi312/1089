<?php

declare(strict_types=1);

namespace App\Admin\SubscriptionPlan;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

final class PriceScheduleAdmin extends AbstractAdmin
{

    protected function configureDatagridFilters(DatagridMapper $datagridMapper): void
    {
        $datagridMapper
            ->add('id')
            ->add('price')
            ->add('minCountOfFTEs')
            ->add('maxCountOfFTEs')
            ;
    }

    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper
            ->add('id')
            ->add('price')
            ->add('nextPrice')
            ->add('nextPriceEffectiveDate')
            ->add('minCountOfFTEs')
            ->add('maxCountOfFTEs')
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
        $formMapper
            ->add('price')
            ->add('minCountOfFTEs')
            ->add('maxCountOfFTEs')
            ->add('nextPrice')
            ->add('nextPriceEffectiveDate')
            ;
    }

    protected function configureShowFields(ShowMapper $showMapper): void
    {
        $showMapper
            ->add('id')
            ->add('price')
            ->add('nextPrice')
            ->add('nextPriceEffectiveDate')
            ->add('minCountOfFTEs')
            ->add('maxCountOfFTEs')
            ;
    }
}
