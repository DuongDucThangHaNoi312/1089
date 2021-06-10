<?php

namespace App\EventListener;

use App\Entity\Stripe\StripeSubscription;
use App\Entity\SubscriptionInterface;
use App\Entity\User\Subscription;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use App\Service\SubscriptionManager;

class SubscriptionCancelledListener implements EventSubscriber {

    /**
     * @var EntityManager $em
     */
    private $em;

    /**
     * @var SubscriptionManager $subscriptionManager
     */
    private $subscriptionManager;

    /**
     *
     * @param \Doctrine\ORM\EntityManager $em
     */
    public function __construct(EntityManager $em, SubscriptionManager $subscriptionManager)
    {
        $this->em = $em;
        $this->subscriptionManager = $subscriptionManager;
    }

    public function getSubscribedEvents()
    {
        return [
            Events::postUpdate,
        ];
    }

    public function postUpdate(LifecycleEventArgs $args) {
        $object = $args->getObject();

        // Determine if it's cancelled
        // 'canceled' or 'unpaid' is based on my Stripe settings under account/billing/automatic subscription status.
        // Stripe has 'canceled', 'incomplete_expired'
        if ($object instanceof SubscriptionInterface) {
            // Cancel Subscriptions
            if (in_array($object->getStatus(), ['canceled', 'incomplete_expired'])) {
                // Fetch the Subscription
                $subscriptionRepository = $this->em->getRepository(Subscription::class);
                $subscription = $subscriptionRepository->findOneBy(['rawStripeSubscription' => $object->getPaymentProcessorId()]);
                if ($subscription != null) {
                    if ($subscription instanceof \App\Entity\User\JobSeekerUser\Subscription) {
                        $this->subscriptionManager->cancelJobSeekerSubscription($subscription);
                    } elseif ($subscription instanceof \App\Entity\City\Subscription) {
                        $this->subscriptionManager->cancelCitySubscription($subscription);
                    }
                }
            }
        }
    }
}