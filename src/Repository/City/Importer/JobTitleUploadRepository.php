<?php

namespace App\Repository\City\Importer;

use App\Entity\City\Importer\JobTitleUpload;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method JobTitleUpload|null find($id, $lockMode = null, $lockVersion = null)
 * @method JobTitleUpload|null findOneBy(array $criteria, array $orderBy = null)
 * @method JobTitleUpload[]    findAll()
 * @method JobTitleUpload[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class JobTitleUploadRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, JobTitleUpload::class);
    }

//    /**
//     * @return JobTitleUpload[] Returns an array of JobTitleUpload objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('j')
            ->andWhere('j.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('j.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?JobTitleUpload
    {
        return $this->createQueryBuilder('j')
            ->andWhere('j.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
