<?php

declare(strict_types=1);

namespace App\Admin\SubscriptionPlan;

use App\Entity\SubscriptionPlan;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

final class JobSeekerSubscriptionPlanAdmin extends AbstractAdmin
{

    protected function configureDatagridFilters(DatagridMapper $datagridMapper): void
    {
        $datagridMapper
            ->add('name')
            ->add('isTrial')
            ->add('isActive')
            ->add('limitCityLinkSearchToCountyOfResidence')
            ;
    }

    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper
            ->addIdentifier('name')
            ->add('price')
            ->add('nextPrice')
            ->add('nextPriceEffectiveDate')
            ->add('renewalFrequency')
            ->add('isTrial')
            ->add('isActive', null, ['editable' => true])
            ->add('limitCityLinkSearchToCountyOfResidence')
            ->add('countSavedSearches')
            ->add('allowedJobLevels', null, [
                'label' => 'Allowed Job Levels to Submit Interest in'
            ])
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
        /** @var SubscriptionPlan $subscriptionPlan */
        $subscriptionPlan = $this->getSubject();
        $isEdit = false;
        if ($subscriptionPlan && $subscriptionPlan->getId()) {
            $isEdit = true;
        }
        $formMapper
            ->add('name')
            ->add('price')
            ->add('nextPrice')
            ->add('nextPriceEffectiveDate', \Symfony\Component\Form\Extension\Core\Type\DateType::class, [
                'placeholder' => [
                    'year' => 'Year', 'month' => 'Month', 'day' => 'Day',
                ],
                'years' => range(date('Y') - 10, date('Y') + 31),
            ])
            ->add('renewalFrequency', EntityType::class,  ['disabled' => $isEdit, 'class' => SubscriptionPlan\Lookup\RenewalFrequency::class,
                 'attr' => ['readonly' => $isEdit]])
            ->add('description')
            ->add('isTrial')
            ->add('isActive')
            ->add('limitCityLinkSearchToCountyOfResidence')
            ->add('countSavedSearches')
            ->add('allowedJobLevels', null, [
                'label' => 'Job Levels Allowed for Alerts and Interest'
            ])
            ;
    }

    protected function configureShowFields(ShowMapper $showMapper): void
    {
        $showMapper
            ->add('id')
            ->add('name')
            ->add('price')
            ->add('nextPrice')
            ->add('nextPriceEffectiveDate')
            ->add('renewalFrequency')
            ->add('description')
            ->add('isTrial')
            ->add('isActive')
            ->add('limitCityLinkSearchToCountyOfResidence')
            ->add('countSavedSearches')
            ->add('allowedJobLevels', null, [
                'label' => 'Allow Job Levels to Submit Interest in'
            ])
            ->add('createdAt')
            ->add('updatedAt')
            ;
    }
}
