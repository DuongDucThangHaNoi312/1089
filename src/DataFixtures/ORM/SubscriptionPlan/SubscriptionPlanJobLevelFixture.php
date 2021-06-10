<?php

namespace App\DataFixtures\ORM\SubscriptionPlan;

use App\DataFixtures\ORM\BaseFixture;
use App\DataFixtures\ORM\JobTitle\Lookup\JobLevelFixture;
use App\DataFixtures\ORM\SubscriptionPlan\RenewalFrequencyFixture;
use App\Entity\JobTitle\Lookup\JobLevel;
use App\Entity\SubscriptionPlan\JobSeekerSubscriptionPlan as JobSeekerSubscriptionPlan;
use App\Entity\SubscriptionPlan\CitySubscriptionPlan as CitySubscriptionPlan;
use App\Entity\SubscriptionPlan;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SubscriptionPlanJobLevelFixture extends BaseFixture implements DependentFixtureInterface, ContainerAwareInterface {

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
        return "SubscriptionPlanJobLevel.csv";
    }

    public function getObject()
    {
        return SubscriptionPlan::class;
    }

    public function getBasePath()
    {
        return parent::getBasePath() . 'SubscriptionPlan/';
    }

    /**
     * @param mixed|SubscriptionPlan $object
     * @param array $value
     * @param array $header
     */
    public function create(&$object, $value = array(), $header = array())
    {
        $manager = $this->getObjectManager();

        $subscriptionPlanRepository = $manager->getRepository(JobSeekerSubscriptionPlan::class);
        /** @var JobSeekerSubscriptionPlan $subscriptionPlan */
        $subscriptionPlan = $subscriptionPlanRepository->find($value[$header['job_seeker_subscription_plan_id']]);
        $object = $subscriptionPlan;

        $jobLevelRepository = $manager->getRepository(JobLevel::class);
        /** @var JobLevel $jobLevel */
        $jobLevel = $jobLevelRepository->find($value[$header['job_level_id']]);

        $object->addAllowedJobLevel($jobLevel);

    }

    public function getDependencies()
    {
        return [
            JobLevelFixture::class,
            SubscriptionPlanFixture::class,
        ];
    }
}