<?php

namespace App\DataFixtures\ORM\SubscriptionPlan;

use App\DataFixtures\ORM\BaseFixture;
use App\Entity\SubscriptionPlan;
use App\Entity\SubscriptionPlan\PriceSchedule;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PriceScheduleFixture extends BaseFixture implements DependentFixtureInterface, ContainerAwareInterface {

    /**
     * @var ContainerInterface
     */
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    private function getObjectManager() {
        return $this->container->get('doctrine.orm.entity_manager');
    }

    public function getFileName()
    {
        return "PriceScheduleFixture.csv";
    }

    public function getObject()
    {
        return PriceSchedule::class;
    }

    public function getBasePath()
    {
        return parent::getBasePath() . "SubscriptionPlan/";
    }

    /**
     * @param mixed|PriceSchedule $object
     * @param array $value
     * @param array $header
     */
    public function create(&$object, $value = array(), $header = array())
    {
        $manager = $this->getObjectManager();
        $subscriptionPlanRepository = $manager->getRepository(SubscriptionPlan::class);
        /** @var SubscriptionPlan $subcriptionPlan */
        $subcriptionPlan = $subscriptionPlanRepository->find($value[$header['subscription_plan_id']]);
        $object->setPrice($value[$header['price']]);
        $object->setMinCountOfFTEs($value[$header['min_count_of_ftes']]);
        $object->setMaxCountOfFTEs($value[$header['max_count_of_ftes']]);
        $object->setSubscriptionPlan($subcriptionPlan);
    }

    public function getDependencies()
    {
        return [
            SubscriptionPlanFixture::class
        ];
    }
}