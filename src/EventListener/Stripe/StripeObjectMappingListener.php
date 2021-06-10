<?php

namespace  App\EventListener\Stripe;

use App\Entity\Stripe\StripeCustomer as Customer;
use App\Entity\Stripe\StripePlan as Plan;
use App\Entity\Stripe\StripeSubscription as Subscription;
use App\Entity\SubscriptionPlan;
use App\Entity\User;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\EventSubscriber;

class StripeObjectMappingListener implements EventSubscriber {

    /**
     * @var EntityManager $em
     */
    private $em;
    /**
     *
     * @param \Doctrine\ORM\EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
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

        if ($object instanceof Customer) {
            $this->linkCustomerToUser($object);
        }

        if ($object instanceof Subscription) {
            $this->linkStripeSubscriptionToSubscription($object);
        }

        if ($object instanceof Plan) {
            $this->linkPlanToSubscriptionPlan($object);
        }
    }

    public function postPersist(LifecycleEventArgs $args) {
        $object = $args->getObject();

        if ($object instanceof Customer) {
            $this->linkCustomerToUser($object);
        }

        if ($object instanceof Subscription) {
            $this->linkStripeSubscriptionToSubscription($object);
        }

        if ($object instanceof Plan) {
            $this->linkPlanToSubscriptionPlan($object);
        }
    }

    public function linkPlanToSubscriptionPlan(Plan $object) {
        $subscriptionPlanRepository = $this->em->getRepository(SubscriptionPlan::class);
        $subscriptionPlan = $subscriptionPlanRepository->find($object->getMetadata()['subscription_plan_id']);
        if ($subscriptionPlan) {
            $subscriptionPlan->setStripePlan($object);
            // If a Stripe Plan is created for a Price Schedule, remember to set the RawStripePlan for it.
            if (isset($object->getMetadata()['price_schedule_id'])) {
                $priceScheduleRepository = $this->em->getRepository(SubscriptionPlan\PriceSchedule::class);
                $priceSchedule = $priceScheduleRepository->find($object->getMetadata()['price_schedule_id']);
                $priceSchedule->setRawStripePlan($object->getStripeId());
                $this->em->persist($priceSchedule);
            } else {
                $subscriptionPlan->setRawStripePlan($object->getStripeId());
            }
            $this->em->persist($subscriptionPlan);
            $this->em->flush();
        }
    }

    public function linkCustomerToUser(Customer $object) {
        $userRepository = $this->em->getRepository(User::class);
        $user = $userRepository->find($object->getMetadata()['user_id']);
        if ($user){
            $user->setStripeCustomer($object);
            $user->setRawStripeCustomer($object->getStripeId());
            $this->em->persist($user);
            $this->em->flush();
        }
    }

    public function linkStripeSubscriptionToSubscription(Subscription $object)
    {
        $userRepository = $this->em->getRepository(User::class);
        $user = $userRepository->find($object->getMetadata()['user_id']);
        if ($user instanceof User\JobSeekerUser) {
            if ($user->getSubscription()) {
                $isPayed = false;
                if ($object->getStatus() == 'active' || $object->getStatus() == 'cancelled') {
                    $isPayed = true;
                }
                $user->getSubscription()->setIsPaid($isPayed);
                $user->setRawStripeCustomer($object->getCustomer());
                $user->getSubscription()->setStripeSubscription($object);
                $this->em->persist($user->getSubscription());
                $this->em->flush();
            }
        } elseif ($user instanceof User\CityUser) {
            $city = $user->getCity();
            if ($city->getSubscription()) {
                $isPayed = false;
                if ($object->getStatus() == 'active' || $object->getStatus() == 'cancelled') {
                    $isPayed = true;
                }
                $city->getSubscription()->setIsPaid($isPayed);
                $city->getSubscription()->setRawStripeSubscription($object->getStripeId());
                $city->getSubscription()->setStripeSubscription($object);
                $this->em->persist($city->getSubscription());
                $this->em->flush();
            }
        }
    }

}
