<?php

namespace App\Repository\City;

use App\Entity\City\OperationHours;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method OperationHours|null find($id, $lockMode = null, $lockVersion = null)
 * @method OperationHours|null findOneBy(array $criteria, array $orderBy = null)
 * @method OperationHours[]    findAll()
 * @method OperationHours[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OperationHoursRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, OperationHours::class);
    }

//    /**
//     * @return OperationHours[] Returns an array of OperationHours objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('o.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?OperationHours
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
