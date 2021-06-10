<?php

namespace App\EventListener;

use App\Entity\SubscriptionInterface;
use App\Entity\User\Subscription;
use App\Service\SubscriptionManager;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;

class SubscriptionRenewsListener implements EventSubscriber {

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
            Events::postPersist,
            Events::postUpdate,
        ];
    }

    public function postUpdate(LifecycleEventArgs $args) {
        $object = $args->getObject();

        if ($object instanceof SubscriptionInterface) {
            if (in_array($object->getStatus(), ['active', 'incomplete', 'past_due', 'trialing'])) {
                // Fetch the Subscription
                $subscriptionRepository = $this->em->getRepository(Subscription::class);
                $subscription = $subscriptionRepository->findOneBy(['rawStripeSubscription' => $object->getPaymentProcessorId()]);
                if ($subscription != null) {
                    $isPaid = $object->getStatus() == 'active' || $object->getStatus() == 'trialing';
                    $timestamp = $object->getCurrentPeriodEnd();
                    $date = new \DateTime('@' . $timestamp);

                    $this->subscriptionManager->renewSubscription($subscription,$date, $isPaid);
                }
            }
        }
    }

    public function postPersist(LifecycleEventArgs $args) {
        $object = $args->getObject();

        if ($object instanceof SubscriptionInterface) {
            if (in_array($object->getStatus(), ['active', 'incomplete', 'past_due', 'trialing'])) {
                // Fetch the Subscription
                $subscriptionRepository = $this->em->getRepository(Subscription::class);
                $subscription = $subscriptionRepository->findOneBy(['rawStripeSubscription' => $object->getPaymentProcessorId()]);
                if ($subscription != null) {
                    $isPaid = $object->getStatus() == 'active' || $object->getStatus() == 'trialing';
                    $timestamp = $object->getCurrentPeriodEnd();
                    $date = new \DateTime('@'. $timestamp);
                    $this->subscriptionManager->renewSubscription($subscription,$date, $isPaid);
                }
            }
        }
    }
}