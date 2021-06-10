<?php

namespace App\Repository\Stripe;

use App\Entity\Stripe\StripeRefund as Refund;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Refund|null find($id, $lockMode = null, $lockVersion = null)
 * @method Refund|null findOneBy(array $criteria, array $orderBy = null)
 * @method Refund[]    findAll()
 * @method Refund[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StripeRefundRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Refund::class);
    }

    // /**
    //  * @return Refund[] Returns an array of Refund objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Refund
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
