<?php

namespace App\Repository\City;

use App\Entity\City\Subscription;
use App\Entity\SubscriptionPlan\CitySubscriptionPlan;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Exception;
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
     * @throws Exception
     */
    public function getTrialSubscriptionsExpiringOn($days)
    {
        $date = new DateTime($days, new \DateTimeZone('UTC'));
        return $this->createQueryBuilder('subscription')
            ->join('subscription.subscriptionPlan', 'subscription_plan')
            ->where('subscription_plan.isTrial = true')
            ->andWhere('subscription.cancelledAt IS NULL')
            ->andWhere('subscription.expiresAt BETWEEN :from AND :to')
            ->setParameter('from', $date->format('Y-m-d') . ' 00:00:00')
            ->setParameter('to', $date->format('Y-m-d') .  ' 23:59:59')
            ->getQuery()
            ->getResult();
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
     * @throws Exception
     */
    public function getSubscriptionsExpiringOn($days)
    {
        $date = new DateTime($days, new \DateTimeZone('UTC'));
        return $this->createQueryBuilder('subscription')
            ->join('subscription.subscriptionPlan', 'subscription_plan')
            ->where('(subscription_plan.isTrial = false OR subscription_plan.isTrial IS NULL)')
            ->andWhere('subscription.cancelledAt IS NULL')
            ->andWhere('subscription.expiresAt BETWEEN :from AND :to')
            ->setParameter('from', $date->format('Y-m-d') . ' 00:00:00')
            ->setParameter('to', $date->format('Y-m-d') .  ' 23:59:59')
            ->getQuery()
            ->getResult()
            ;
    }

    public function findBySubscriptionPlan(CitySubscriptionPlan $plan) {
        return $this->createQueryBuilder('subscription')
            ->join('subscription.subscriptionPlan', 'subscription_plan')
            ->andWhere('subscription_plan.id = :subscription_plan_id')
            ->setParameter('subscription_plan_id', $plan->getId())
            ->getQuery()
            ->getResult()
            ;
    }
}
