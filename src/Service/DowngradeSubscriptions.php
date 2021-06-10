<?php

namespace App\Service;

use App\Entity\SubscriptionPlan\JobSeekerSubscriptionPlan;
use App\Entity\User\JobSeekerUser;
use App\Entity\User\SubscriptionChangeRequest;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

class DowngradeSubscriptions
{
    /** @var EntityManagerInterface $em */
    private $em;

    /** @var SubscriptionManager $subscriptionManager */
    private $subscriptionManager;

    private $flashbag;

    public function __construct(EntityManagerInterface $em, SubscriptionManager $subscriptionManager, FlashBagInterface $flashbag)
    {
        $this->em                  = $em;
        $this->subscriptionManager = $subscriptionManager;
        $this->flashbag            = $flashbag;
    }

    public function downgradeSubscriptions()
    {
        // Get Subscription Change Requests in which will renew in the next 1 hour
        $subscriptionChangeRequests = $this->em->getRepository(SubscriptionChangeRequest::class)->findAllWithSubscriptionExpiringInOneHr();

        /** @var SubscriptionChangeRequest $subscriptionChangeRequest */
        foreach ($subscriptionChangeRequests as $subscriptionChangeRequest) {

            $subscription = $subscriptionChangeRequest->getSubscription();

            if ($subscription instanceof \App\Entity\City\Subscription) {
                $isSubscribed = false;
                $isPaid       = false;
                $expiresOn    = null;
                $statusArray  = $this->subscriptionManager->processCitySubscription($subscription->getCity(), $subscriptionChangeRequest->getNewSubscriptionPlan(), $this->flashbag);
                extract($statusArray);
                if ($isSubscribed) {
                    $this->subscriptionManager->subscribeCity($subscription->getCity(), $subscriptionChangeRequest->getNewSubscriptionPlan(), $isPaid, $expiresOn);
                } else {
                    // Remove the change request if it wasn't possible?
                }
            } else if ($subscription instanceof \App\Entity\User\JobSeekerUser\Subscription) {

                $isSubscribed = false;
                $isPaid       = false;
                $expiresOn    = null;
                $statusArray  = $this->subscriptionManager->processJobSeekerSubscription($subscription->getJobSeekerUser(), $subscriptionChangeRequest->getNewSubscriptionPlan(), $this->flashbag);
                extract($statusArray);

                if ($isSubscribed) {
                    $this->subscriptionManager->subscribeJobSeeker($subscription->getJobSeekerUser(), $subscriptionChangeRequest->getNewSubscriptionPlan(), $isPaid, false, $expiresOn);
                } else {
                    // Remove the change request if it wasn't possible?
                }
            }
        }
    }


}