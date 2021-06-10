<?php

namespace App\DataFixtures\ORM\SubscriptionPlan;

use App\DataFixtures\ORM\BaseFixture;
use App\DataFixtures\ORM\SubscriptionPlan\RenewalFrequencyFixture;
use App\Entity\SubscriptionPlan\JobSeekerSubscriptionPlan as JobSeekerSubscriptionPlan;
use App\Entity\SubscriptionPlan\CitySubscriptionPlan as CitySubscriptionPlan;
use App\Entity\SubscriptionPlan;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SubscriptionPlanFixture extends BaseFixture implements DependentFixtureInterface, ContainerAwareInterface {

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
        return "SubscriptionPlanFixture.csv";
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
        $renewalFrequencyRepository = $manager->getRepository(SubscriptionPlan\Lookup\RenewalFrequency::class);
        /** @var SubscriptionPlan\Lookup\RenewalFrequency $renewalFrequency */
        $renewalFrequency = $renewalFrequencyRepository->find($value[$header['renewal_frequency_id']]);

        switch($value[$header['type']]) {
            case 'job-seeker':
                $object = new JobSeekerSubscriptionPlan();
                $object->setName($value[$header['name']]);
                $object->setIsActive($value[$header['is_active']]);
                $object->setPrice($value[$header['price']]);
                $object->setIsTrial($value[$header['is_trial']]);
                $object->setRenewalFrequency($renewalFrequency);
                $object->setLimitCityLinkSearchToCountyOfResidence($value[$header['limit_city_link_search_to_county_of_residence']]);
                break;
            case 'city':
                $object = new CitySubscriptionPlan();
                $object->setName($value[$header['name']]);
                $object->setIsActive($value[$header['is_active']]);
                $object->setIsTrial($value[$header['is_trial']]);
                $object->setPrice($value[$header['price']]);
                $object->setAllowedChangeHideExecutiveSeniorJobLevelPositions($value[$header['allowed_change_hide_executive_senior_job_level_positions']]);
                $object->setAllowedActiveJobPostings($value[$header['allowed_active_job_postings']] ? (int)$value[$header['allowed_active_job_postings']] : null );
                $object->setHasJobTitleMaintenanceRequirement($value[$header['has_job_title_maintenance_requirement']]);
                $object->setJobTitleMaintenancePercentage($value[$header['job_title_maintenance_percentage']]);
                $object->setCountOfAllowedUsers($value[$header['count_of_allowed_users']] ? (int)$value[$header['count_of_allowed_users']] : null);
                $object->setJobsOfInterestStars($value[$header['jobs_of_interest_stars']]);
                $object->setHasSearchResumeLimitation($value[$header['has_search_resume_limitation']]);
                $object->setHasSearchCityLinksLimitation($value[$header['has_search_city_links_limitation']]);
                $object->setRenewalFrequency($renewalFrequency);
                break;
            default:
                break;
        }

    }

    public function getDependencies()
    {
        return [
            RenewalFrequencyFixture::class,
        ];
    }
}