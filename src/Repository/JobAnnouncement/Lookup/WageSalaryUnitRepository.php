<?php

namespace App\Repository\JobAnnouncement\Lookup;

use App\Entity\JobAnnouncement\Lookup\WageSalaryUnit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method WageSalaryUnit|null find($id, $lockMode = null, $lockVersion = null)
 * @method WageSalaryUnit|null findOneBy(array $criteria, array $orderBy = null)
 * @method WageSalaryUnit[]    findAll()
 * @method WageSalaryUnit[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WageSalaryUnitRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, WageSalaryUnit::class);
    }

//    /**
//     * @return WageSalaryUnit[] Returns an array of WageSalaryUnit objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('w')
            ->andWhere('w.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('w.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?WageSalaryUnit
    {
        return $this->createQueryBuilder('w')
            ->andWhere('w.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
