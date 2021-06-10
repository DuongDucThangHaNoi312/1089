<?php

namespace App\Repository\SubscriptionPlan;

use App\Entity\SubscriptionPlan\CitySubscriptionPlan;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method CitySubscriptionPlan|null find($id, $lockMode = null, $lockVersion = null)
 * @method CitySubscriptionPlan|null findOneBy(array $criteria, array $orderBy = null)
 * @method CitySubscriptionPlan[]    findAll()
 * @method CitySubscriptionPlan[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CitySubscriptionPlanRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CitySubscriptionPlan::class);
    }

//    /**
//     * @return CitySubscriptionPlan[] Returns an array of CitySubscriptionPlan objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?CitySubscriptionPlan
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
