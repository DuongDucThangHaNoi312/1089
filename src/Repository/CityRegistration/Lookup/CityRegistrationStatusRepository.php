<?php

namespace App\Repository\CityRegistration\Lookup;

use App\Entity\CityRegistration\Lookup\CityRegistrationStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method CityRegistrationStatus|null find($id, $lockMode = null, $lockVersion = null)
 * @method CityRegistrationStatus|null findOneBy(array $criteria, array $orderBy = null)
 * @method CityRegistrationStatus[]    findAll()
 * @method CityRegistrationStatus[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CityRegistrationStatusRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CityRegistrationStatus::class);
    }

//    /**
//     * @return CityRegistrationStatus[] Returns an array of CityRegistrationStatus objects
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
    public function findOneBySomeField($value): ?CityRegistrationStatus
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
