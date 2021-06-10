<?php

namespace App\Repository\Resume;

use App\Entity\Resume\LicenseCertification;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method LicenseCertification|null find($id, $lockMode = null, $lockVersion = null)
 * @method LicenseCertification|null findOneBy(array $criteria, array $orderBy = null)
 * @method LicenseCertification[]    findAll()
 * @method LicenseCertification[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LicenseCertificationRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, LicenseCertification::class);
    }

//    /**
//     * @return LicenseCertification[] Returns an array of LicenseCertification objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('l.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?LicenseCertification
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
