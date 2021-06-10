<?php

namespace App\Repository;

use App\Entity\CityRegistration;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method CityRegistration|null find($id, $lockMode = null, $lockVersion = null)
 * @method CityRegistration|null findOneBy(array $criteria, array $orderBy = null)
 * @method CityRegistration[]    findAll()
 * @method CityRegistration[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CityRegistrationRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CityRegistration::class);
    }

//    /**
//     * @return CityRegistration[] Returns an array of CityRegistration objects
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
    public function findOneBySomeField($value): ?CityRegistration
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
