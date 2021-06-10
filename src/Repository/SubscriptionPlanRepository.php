<?php

namespace App\Repository;

use App\Entity\SubscriptionPlan;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method SubscriptionPlan|null find($id, $lockMode = null, $lockVersion = null)
 * @method SubscriptionPlan|null findOneBy(array $criteria, array $orderBy = null)
 * @method SubscriptionPlan[]    findAll()
 * @method SubscriptionPlan[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SubscriptionPlanRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, SubscriptionPlan::class);
    }

    public function findAllWithNextPriceEffectiveDateToday() {
        $today = new \DateTime('now', new \DateTimeZone('UTC'));
        $queryBuilder = $this->createQueryBuilder('subscription_plan')
            ->andWhere('subscription_plan.nextPriceEffectiveDate = :today')
            ->setParameter('today', $today->format('Y-m-d'));
        return $queryBuilder
            ->getQuery()
            ->getResult();
    }

//    /**
//     * @return SubscriptionPlan[] Returns an array of SubscriptionPlan objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?SubscriptionPlan
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
