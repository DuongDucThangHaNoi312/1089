<?php

namespace App\Repository\SubscriptionPlan;

use App\Entity\SubscriptionPlan\RenewalFrequency;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method RenewalFrequency|null find($id, $lockMode = null, $lockVersion = null)
 * @method RenewalFrequency|null findOneBy(array $criteria, array $orderBy = null)
 * @method RenewalFrequency[]    findAll()
 * @method RenewalFrequency[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RenewalFrequencyRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, RenewalFrequency::class);
    }

//    /**
//     * @return RenewalFrequency[] Returns an array of RenewalFrequency objects
//     */
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
    public function findOneBySomeField($value): ?RenewalFrequency
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
