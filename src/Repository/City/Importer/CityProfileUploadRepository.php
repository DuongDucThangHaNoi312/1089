<?php

namespace App\Repository\City\Importer;

use App\Entity\City\Importer\CityProfileUpload;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method CityProfileUpload|null find($id, $lockMode = null, $lockVersion = null)
 * @method CityProfileUpload|null findOneBy(array $criteria, array $orderBy = null)
 * @method CityProfileUpload[]    findAll()
 * @method CityProfileUpload[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CityProfileUploadRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CityProfileUpload::class);
    }

//    /**
//     * @return CityProfileUpload[] Returns an array of CityProfileUpload objects
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
    public function findOneBySomeField($value): ?CityProfileUpload
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
