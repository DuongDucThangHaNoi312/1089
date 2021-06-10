<?php

namespace App\Repository\User;

use App\Entity\User\SubscriptionChangeRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method SubscriptionChangeRequest|null find($id, $lockMode = null, $lockVersion = null)
 * @method SubscriptionChangeRequest|null findOneBy(array $criteria, array $orderBy = null)
 * @method SubscriptionChangeRequest[]    findAll()
 * @method SubscriptionChangeRequest[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SubscriptionChangeRequestRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, SubscriptionChangeRequest::class);
    }

    // /**
    //  * @return SubscriptionChangeRequestQueue[] Returns an array of SubscriptionChangeRequestQueue objects
    //  */
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
    public function findOneBySomeField($value): ?SubscriptionChangeQueue
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function findAllWithSubscriptionExpiringInOneHr() {
        $oneHrAfter = new \DateTime('now', new\DateTimeZone('UTC'));
        $oneHrAfter = $oneHrAfter->modify('+1 hour');

        return $this->createQueryBuilder('subscription_change_request')
            ->andWhere('subscription_change_request.changeOn <= :endDate')
            ->setParameter('endDate', $oneHrAfter->format('Y-m-d H:i:s'))
            ->getQuery()
            ->getResult();
    }
}
