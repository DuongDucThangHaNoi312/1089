<?php

namespace App\Repository\JobAnnouncement\Lookup;

use App\Entity\JobAnnouncement\Lookup\JobAnnouncementStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method JobAnnouncementStatus|null find($id, $lockMode = null, $lockVersion = null)
 * @method JobAnnouncementStatus|null findOneBy(array $criteria, array $orderBy = null)
 * @method JobAnnouncementStatus[]    findAll()
 * @method JobAnnouncementStatus[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class JobAnnouncementStatusRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, JobAnnouncementStatus::class);
    }

//    /**
//     * @return JobAnnouncementStatus[] Returns an array of JobAnnouncementStatus objects
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
    public function findOneBySomeField($value): ?JobAnnouncementStatus
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
