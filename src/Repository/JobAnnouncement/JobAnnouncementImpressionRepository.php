<?php

namespace App\Repository\JobAnnouncement;

use App\Entity\JobAnnouncement\JobAnnouncementImpression;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method JobAnnouncementImpression|null find($id, $lockMode = null, $lockVersion = null)
 * @method JobAnnouncementImpression|null findOneBy(array $criteria, array $orderBy = null)
 * @method JobAnnouncementImpression[]    findAll()
 * @method JobAnnouncementImpression[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class JobAnnouncementImpressionRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, JobAnnouncementImpression::class);
    }

    public function getJobAnnouncementImpressionInPeriod($jaId, $from, $to)
    {
        $qb = $this->createQueryBuilder('v')
                   ->select('COUNT(DISTINCT v.id) AS impressionCount')
                   ->where('v.jobAnnouncement = :ja')
                   ->setParameter('ja', $jaId);

        if ($from) {
            $qb->andWhere('v.createdAt >= :from')
               ->setParameter('from', $from);
        }

        if ($to) {
            $qb->andWhere('v.createdAt <= :to')
               ->setParameter('to', $to);
        }

        return $qb->getQuery()->getSingleScalarResult();
    }

    // /**
    //  * @return JobAnnouncementImpression[] Returns an array of JobAnnouncementImpression objects
    //  */
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
    public function findOneBySomeField($value): ?JobAnnouncementImpression
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
