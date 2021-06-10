<?php

namespace App\Repository\JobAnnouncement;

use App\Entity\JobAnnouncement\View;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method View|null find($id, $lockMode = null, $lockVersion = null)
 * @method View|null findOneBy(array $criteria, array $orderBy = null)
 * @method View[]    findAll()
 * @method View[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ViewRepository extends ServiceEntityRepository
{
    /**
     * ViewRepository constructor.
     *
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, View::class);
    }

    /**
     * @param $cityId
     * @param null $from
     * @param null $to
     *
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getCountOfClicksOnJobAnnouncementPages($cityId, $from = null, $to = null)
    {
        $qb = $this->createQueryBuilder('v')
                   ->select('COUNT(DISTINCT v.jobSeekerUser) AS cntUserViews')
                   ->join('v.jobAnnouncement', 'ja')
                   ->join('ja.city', 'c')
                   ->where('c.id = :cityID')
                   ->setParameter('cityID', $cityId);

        if ($from && $to) {
            $qb->andWhere('v.createdAt BETWEEN :from AND :to')
               ->setParameter('from', $from)
               ->setParameter('to', $to);
        }

        return $qb->getQuery()->getSingleScalarResult();
    }

    public function getJobAnnouncementViewInPeriod($jaId, $from = null, $to = null)
    {
        $qb = $this->createQueryBuilder('v')
                   ->select('COUNT(DISTINCT v.id) AS viewCount')
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

    /**
     * @param $jobAnnouncementId
     * @param $endsOn
     */
    public function deleteExpiredJobViewCount($jobAnnouncementId, $endsOn)
    {
        $stmt = $this->_em->getConnection()->prepare('DELETE FROM job_announcement_view WHERE job_announcement_id = :jobId AND created_at > :endsOn');
        $stmt->bindValue('jobId', $jobAnnouncementId);
        $stmt->bindValue('endsOn', $endsOn->format('Y-m-d h:i:s'));
        $stmt->execute();
    }
}
