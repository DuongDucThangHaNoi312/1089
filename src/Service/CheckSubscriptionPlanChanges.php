<?php

namespace App\Service;

use App\Entity\SubscriptionPlan;
use App\Repository\SubscriptionPlan\PriceScheduleRepository;
use App\Repository\SubscriptionPlanRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

class CheckSubscriptionPlanChanges {
    /** @var EntityManagerInterface $em */
    private $em;

    /** @var SubscriptionProcessorInterface $subscriptionProcessor */
    private $subscriptionProcessor;

    private $flashBag;

    public function __construct(EntityManagerInterface $em, SubscriptionProcessorInterface $subscriptionProcessor, FlashBagInterface $flashBag)
    {
        $this->flashBag = $flashBag;
        $this->subscriptionProcessor = $subscriptionProcessor;
        $this->subscriptionProcessor->setFlashBag($flashBag);
        $this->em = $em;
    }

    public function check() {
        /** @var SubscriptionPlanRepository $subscriptionPlanRepository */
        $subscriptionPlanRepository = $this->em->getRepository(SubscriptionPlan::class);
        $subscriptionPlans = $subscriptionPlanRepository->findAllWithNextPriceEffectiveDateToday();

        // For all Changed Subscription Plans
        foreach ($subscriptionPlans as $subscriptionPlan) {
            // Find all Subscriptions in the system following this plan
            if ($subscriptionPlan instanceof SubscriptionPlan\JobSeekerSubscriptionPlan) {
                // Create Stripe Plan
                /** @var \Stripe\Plan $plan */
                $plan = $this->subscriptionProcessor->updatePlan($subscriptionPlan);
                $subscriptionPlan->setRawStripePlan($plan->id);
                /** @var \App\Repository\User\JobSeekerUser\SubscriptionRepository $subscriptionRepository */
                $subscriptionRepository = $this->em->getRepository(\App\Entity\User\JobSeekerUser\Subscription::class);
                $subscriptions = $subscriptionRepository->findBySubscriptionPlan($subscriptionPlan);
                // Get Subscriptions Following Subscription
                /** @var \App\Entity\User\JobSeekerUser\Subscription $subscription */
                foreach ($subscriptions as $subscription) {
                    // Get Stripe Subscription
                    $stripeSubscription = $this->subscriptionProcessor->retrieveSubscription($subscription->getPaymentProcessorSubscriptionId());
                    // Update Subscription
                    if ($stripeSubscription && $plan) {
                        $this->subscriptionProcessor->updateSubscriptionAfterPlanChange($stripeSubscription, $plan);
                    }
                }
            }

            if ($subscriptionPlan instanceof SubscriptionPlan\CitySubscriptionPlan) {
                /** @var \App\Repository\City\SubscriptionRepository $subscriptionRepository */
                $subscriptionRepository = $this->em->getRepository(\App\Entity\City\Subscription::class);
                $subscriptions = $subscriptionRepository->findBySubscriptionPlan($subscriptionPlan);
                /** @var \App\Entity\City\Subscription $subscription */
                foreach ($subscriptions as $subscription) {
                    $city = $subscription->getCity();
                    $plan = $this->subscriptionProcessor->updateCityPlan($subscriptionPlan, $city);
                    $subscriptionPlan->setRawStripePlan($plan->id);
                    $stripeSubscription = $this->subscriptionProcessor->retrieveSubscription($subscription->getPaymentProcessorSubscriptionId());
                    if ($stripeSubscription && $plan) {
                        $this->subscriptionProcessor->updateSubscriptionAfterPlanChange($stripeSubscription, $plan);
                    }
                }

            }

            $nextPrice = $subscriptionPlan->getNextPrice();
            $subscriptionPlan->setPrice($nextPrice);
            $subscriptionPlan->setNextPrice(null);
            $subscriptionPlan->setNextPriceEffectiveDate(null);
            $this->em->persist($subscriptionPlan);
            $this->em->flush();
        }

        /** @var PriceScheduleRepository $priceScheduleRepository */
        $priceScheduleRepository = $this->em->getRepository(SubscriptionPlan\PriceSchedule::class);
        $priceSchedules = $priceScheduleRepository->findAllWithNextPriceEffectiveDateToday();

        /** @var SubscriptionPlan\PriceSchedule  $priceSchedule */
        foreach($priceSchedules as $priceSchedule) {
            /** @var SubscriptionPlan\CitySubscriptionPlan $subscriptionPlan */
            $subscriptionPlan =  $priceSchedule->getSubscriptionPlan();
            if ($subscriptionPlan instanceof SubscriptionPlan\CitySubscriptionPlan) {
                /** @var \App\Repository\City\SubscriptionRepository $subscriptionRepository */
                $subscriptionRepository = $this->em->getRepository(\App\Entity\City\Subscription::class);
                $subscriptions = $subscriptionRepository->findBySubscriptionPlan($subscriptionPlan);
                /** @var \App\Entity\City\Subscription $subscription */
                foreach ($subscriptions as $subscription) {
                    $city = $subscription->getCity();
                    $plan = $this->subscriptionProcessor->updateCityPlan($subscriptionPlan, $city);
                    $priceSchedule->setRawStripePlan($plan->id);
                    $stripeSubscription = $this->subscriptionProcessor->retrieveSubscription($subscription->getPaymentProcessorSubscriptionId());
                    if ($stripeSubscription && $plan) {
                        $this->subscriptionProcessor->updateSubscriptionAfterPlanChange($stripeSubscription, $plan);
                    }
                }
            }


            $nextPrice = $priceSchedule->getNextPrice();
            $priceSchedule->setPrice($nextPrice);
            $priceSchedule->setNextPrice(null);
            $priceSchedule->setNextPriceEffectiveDate(null);
            $this->em->persist($subscriptionPlan);
            $this->em->flush();

        }
    }
}