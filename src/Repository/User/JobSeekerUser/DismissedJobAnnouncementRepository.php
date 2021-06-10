<?php

namespace App\Repository\User\JobSeekerUser;

use App\Entity\User\JobSeekerUser\DismissedJobAnnouncement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method DismissedJobAnnouncement|null find($id, $lockMode = null, $lockVersion = null)
 * @method DismissedJobAnnouncement|null findOneBy(array $criteria, array $orderBy = null)
 * @method DismissedJobAnnouncement[]    findAll()
 * @method DismissedJobAnnouncement[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DismissedJobAnnouncementRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, DismissedJobAnnouncement::class);
    }

    // /**
    //  * @return DismissedJobAnnouncement[] Returns an array of DismissedJobAnnouncement objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?DismissedJobAnnouncement
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
