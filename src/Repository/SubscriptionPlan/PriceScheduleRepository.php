<?php

namespace App\Repository\SubscriptionPlan;

use App\Entity\SubscriptionPlan\PriceSchedule;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method PriceSchedule|null find($id, $lockMode = null, $lockVersion = null)
 * @method PriceSchedule|null findOneBy(array $criteria, array $orderBy = null)
 * @method PriceSchedule[]    findAll()
 * @method PriceSchedule[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PriceScheduleRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, PriceSchedule::class);
    }


    public function findAllWithNextPriceEffectiveDateToday() {
        $today = new \DateTime('now', new \DateTimeZone('UTC'));
        $queryBuilder = $this->createQueryBuilder('price_schedule')
            ->andWhere('price_schedule.nextPriceEffectiveDate = :today')
            ->setParameter('today', $today->format('Y-m-d'));
        return $queryBuilder
            ->getQuery()
            ->getResult();

    }
//    /**
//     * @return PriceSchedule[] Returns an array of PriceSchedule objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?PriceSchedule
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
