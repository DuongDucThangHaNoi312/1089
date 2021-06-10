<?php

namespace App\Repository\City\Importer;

use App\Entity\City\Importer\CityUpload;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method CityUpload|null find($id, $lockMode = null, $lockVersion = null)
 * @method CityUpload|null findOneBy(array $criteria, array $orderBy = null)
 * @method CityUpload[]    findAll()
 * @method CityUpload[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CityUploadRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CityUpload::class);
    }

//    /**
//     * @return CityUpload[] Returns an array of CityUpload objects
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
    public function findOneBySomeField($value): ?CityUpload
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
