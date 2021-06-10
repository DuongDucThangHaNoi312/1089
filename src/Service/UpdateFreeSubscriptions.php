<?php

namespace App\Service;

use App\Entity\City\Subscription as CitySubscription;
use App\Entity\User\JobSeekerUser\Subscription as JobSeekerSubscription;
use Doctrine\ORM\EntityManagerInterface;
use DateTime;

class UpdateFreeSubscriptions {

    /** @var EntityManagerInterface $em */
    private $em;

    /** @var SubscriptionManager $subscriptionManager */
    private $subscriptionManager;

    public function __construct(EntityManagerInterface $em, SubscriptionManager $subscriptionManager)
    {
        $this->em = $em;
        $this->subscriptionManager = $subscriptionManager;
    }

    public function renewCitySubscriptions() {
        $subscriptionRepository = $this->em->getRepository(CitySubscription::class);
        $subscriptions = $subscriptionRepository->getFreeSubscriptionsExpiringToday();

        /** @var CitySubscription $subscription */
        foreach ($subscriptions as $subscription) {
            $date = new DateTime('+1 ' . $subscription->getSubscriptionPlan()->getRenewalFrequency()->determineInterval(), new \DateTimeZone('UTC'));
            $this->subscriptionManager->renewSubscription($subscription, $date, true);
        }
    }

    public function cancelCitySubscriptions() {
        $subscriptionRepository = $this->em->getRepository(CitySubscription::class);
        $subscriptions = $subscriptionRepository->getFreeSubscriptionsExpiringToday();

        /** @var CitySubscription $subscription */
        foreach ($subscriptions as $subscription) {
            $this->subscriptionManager->cancelCitySubscription($subscription);
        }
    }

    public function renewJobSeekerSubscriptions() {
        $subscriptionRepository = $this->em->getRepository(JobSeekerSubscription::class);
        $subscriptions = $subscriptionRepository->getFreeSubscriptionsExpiringToday();

        /** @var JobSeekerSubscription $subscription */
        foreach ($subscriptions as $subscription) {
            $date = new DateTime('+1 ' . $subscription->getSubscriptionPlan()->getRenewalFrequency()->determineInterval(), new \DateTimeZone('UTC'));
            $this->subscriptionManager->renewSubscription($subscription, $date, true);
        }

    }

    public function cancelJobSeekerSubscriptions() {
        $subscriptionRepository = $this->em->getRepository(JobSeekerSubscription::class);
        $subscriptions = $subscriptionRepository->getFreeSubscriptionsExpiringToday();

        /** @var JobSeekerSubscription $subscription */
        foreach ($subscriptions as $subscription) {
            $this->subscriptionManager->cancelJobSeekerSubscription($subscription);
        }
    }
}