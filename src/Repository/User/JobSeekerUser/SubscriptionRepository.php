<?php

namespace App\Repository\User\JobSeekerUser;

use App\Entity\SubscriptionPlan;
use App\Entity\User\JobSeekerUser\Subscription;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Subscription|null find($id, $lockMode = null, $lockVersion = null)
 * @method Subscription|null findOneBy(array $criteria, array $orderBy = null)
 * @method Subscription[]    findAll()
 * @method Subscription[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SubscriptionRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Subscription::class);
    }

    /**
     * @param $days
     * @return mixed
     * @throws \Exception
     */
    public function getTrialSubscriptionsExpiringOn($days)
    {
        $date = new \DateTime($days, new \DateTimeZone('UTC'));
        return $this->createQueryBuilder('subscription')
            ->join('subscription.subscriptionPlan', 'subscription_plan')
            ->where('subscription_plan.isTrial = true')
            ->andWhere('subscription.expiresAt >= :startDate')
            ->andWhere('subscription.expiresAt <= :endDate')
            ->andWhere('subscription.cancelledAt IS NULL')
            ->setParameter('startDate', $date->format('Y-m-d 00:00:00'))
            ->setParameter('endDate', $date->format('Y-m-d 23:59:59'))
            ->getQuery()
            ->getResult()
            ;
    }

    public function getFreeSubscriptionsExpiringToday() {
        // Only those Subscriptions that are not managed by Stripe
        $today = new \DateTime('now', new \DateTimeZone('UTC'));
        return $this->createQueryBuilder('subscription')
            ->join('subscription.subscriptionPlan', 'subscription_plan')
            ->where ('subscription_plan.price = 0.00')
            ->andWhere('subscription.rawStripeSubscription IS NULL')
            ->andWhere('subscription.expiresAt >= :startDate')
            ->andWhere('subscription.expiresAt <= :endDate')
            ->andWhere('subscription.cancelledAt IS NULL')
            ->andWhere('subscription_plan.isTrial = false')
            ->setParameter('startDate', $today->format('Y-m-d 00:00:00'))
            ->setParameter('endDate', $today->format('Y-m-d 23:59:59'))
            ->getQuery()
            ->getResult();
    }

    public function getFreeSubscriptionsCancellingToday() {
        // Only those Subscriptions that are not managed by Stripe
        $today = new \DateTime('now', new \DateTimeZone('UTC'));
        return $this->createQueryBuilder('subscription')
            ->join('subscription.subscriptionPlan', 'subscription_plan')
            ->where ('subscription_plan.price = 0.00')
            ->andWhere('subscription.rawStripeSubscription IS NULL')
            ->andWhere('subscription.willCancelOn >= :startDate')
            ->andWhere('subscription.willCancelOn <= :endDate')
            ->andWhere('subscription.cancelledAt IS NULL')
            ->andWhere('subscription_plan.isTrial = false')
            ->setParameter('startDate', $today->format('Y-m-d 00:00:00'))
            ->setParameter('endDate', $today->format('Y-m-d 23:59:59'))
            ->getQuery()
            ->getResult();
    }

    /**
     * @param $days
     * @return mixed
     * @throws \Exception
     */
    public function getSubscriptionsExpiringOn($days)
    {
        $date = new \DateTime($days, new \DateTimeZone('UTC'));
        return $this->createQueryBuilder('subscription')
            ->join('subscription.subscriptionPlan', 'subscription_plan')
            ->where('(subscription_plan.isTrial = false OR subscription_plan.isTrial IS NULL)')
            ->andWhere('subscription.expiresAt >= :startDate')
            ->andWhere('subscription.expiresAt <= :endDate')
            ->andWhere('subscription.cancelledAt IS NULL')
            ->setParameter('startDate', $date->format('Y-m-d 00:00:00'))
            ->setParameter('endDate', $date->format('Y-m-d 23:59:59'))
            ->getQuery()
            ->getResult()
            ;
    }

    public function findBySubscriptionPlan(SubscriptionPlan $plan) {
        return $this->createQueryBuilder('subscription')
            ->join('subscription.subscriptionPlan', 'subscription_plan')
            ->andWhere('subscription_plan.id = :subscription_plan_id')
            ->setParameter('subscription_plan_id', $plan->getId())
            ->getQuery()
            ->getResult()
        ;
    }

    public function getCancelledSubscriptionPlan() {
        $today = new \DateTime('now', new \DateTimeZone('UTC'));
        return $this->createQueryBuilder('subscription')
                    ->join('subscription.subscriptionPlan', 'subscription_plan')
                    ->where ('subscription_plan.price > 0')
                    ->andWhere('subscription.cancelledAt <= :today')
                    ->andWhere('subscription_plan.isTrial = false')
                    ->setParameter('today', $today->format('Y-m-d h:m:i'))
                    ->getQuery()
                    ->getResult();
    }

}
