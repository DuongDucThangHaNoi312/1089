<?php

declare(strict_types=1);

namespace App\Admin\SubscriptionPlan;

use App\Entity\SubscriptionPlan;
use App\Entity\SubscriptionPlan\CitySubscriptionPlan;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\Form\Type\CollectionType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

final class CitySubscriptionPlanAdmin extends AbstractAdmin
{

    protected function configureDatagridFilters(DatagridMapper $datagridMapper): void
    {
        $datagridMapper
            ->add('name')
            ->add('isActive')
            ;
    }

    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper
            ->add('name')
            ->add('price', null, [
                'label' => 'Price - Set to 0 for Tiered Pricing'
            ])
            ->add('nextPrice')
            ->add('nextPriceEffectiveDate')
            ->add('renewalFrequency')
            ->add('isTrial')
            ->add('isActive', null, ['editable' => true])
            ->add('allowedActiveJobPostings')
            ->add('hasJobTitleMaintenanceRequirement')
            ->add('jobTitleMaintenancePercentage')
            ->add('countOfAllowedUsers')
            //->add('jobsOfInterestStars')
            ->add('hasSearchResumeLimitation')
            ->add('hasSearchCityLinksLimitation')
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
            ->add('price', null, [
                'label' => 'Price - Set to 0 for Tiered Pricing'
            ])
            ->add('nextPrice')
            ->add('nextPriceEffectiveDate', \Symfony\Component\Form\Extension\Core\Type\DateType::class, [
                'placeholder' => [
                    'year' => 'Year', 'month' => 'Month', 'day' => 'Day',
                ],
                'years' => range(date('Y') - 10, date('Y') + 31),
            ])
            ->add('renewalFrequency', EntityType::class,  ['disabled' => $isEdit, 'class' => SubscriptionPlan\Lookup\RenewalFrequency::class,
                'attr' => ['readonly' => $isEdit]])
            ->add('isTrial')
            ->add('isActive')
            ->add('description')
            ->add('allowedActiveJobPostings')
            ->add('allowedChangeHideExecutiveSeniorJobLevelPositions', null, ['label' => 'allow Cities to Hide Executive Senior Job Titles'])
            ->add('hasJobTitleMaintenanceRequirement')
            ->add('jobTitleMaintenancePercentage')
            ->add('countOfAllowedUsers')
            //->add('jobsOfInterestStars')
            ->add('hasSearchResumeLimitation')
            ->add('hasSearchCityLinksLimitation')
            ->add('priceSchedules', CollectionType::class, [
                'required' => false,
                'by_reference' => true,
                'label' => 'FTE Based Price Schedule',
            ], [
                'edit' => 'inline',
                'inline' => 'table',
            ])
            ;
    }

    protected function configureShowFields(ShowMapper $showMapper): void
    {
        $showMapper
            ->add('id')
            ->add('name')
            ->add('price', null, [
                'label' => 'Price - Set to 0 for Tiered Pricing'
            ])
            ->add('nextPrice')
            ->add('nextPriceEffectiveDate')
            ->add('renewalFrequency')
            ->add('isTrial')
            ->add('isActive')
            ->add('description')
            ->add('createdAt')
            ->add('updatedAt')
            ->add('allowedActiveJobPostings')
            ->add('allowedChangeHideExecutiveSeniorJobLevelPositions', null, ['label' => 'allow Cities to Hide Executive Senior Job Titles'])
            ->add('hasJobTitleMaintenanceRequirement')
            ->add('jobTitleMaintenancePercentage')
            ->add('countOfAllowedUsers')
            ->add('jobsOfInterestStars')
            ->add('hasSearchResumeLimitation')
            ->add('hasSearchCityLinksLimitation')
            ;
    }

    /**
     * @param CitySubscriptionPlan $object
     */
    private function doCommonPre(CitySubscriptionPlan $object)
    {
        foreach ($object->getPriceSchedules() as $ps) {
            $ps->setSubscriptionPlan($object);
        }
    }

    public function preUpdate($object)
    {
        $this->doCommonPre($object);
    }

    public function prePersist($object)
    {
        $this->doCommonPre($object);
    }

}
