<?php

namespace App\Repository;

use App\Entity\AlertedJobAnnouncement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method AlertedJobAnnouncement|null find($id, $lockMode = null, $lockVersion = null)
 * @method AlertedJobAnnouncement|null findOneBy(array $criteria, array $orderBy = null)
 * @method AlertedJobAnnouncement[]    findAll()
 * @method AlertedJobAnnouncement[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AlertedJobAnnouncementRepository extends ServiceEntityRepository
{
    /**
     * AlertedJobAnnouncementRepository constructor.
     *
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, AlertedJobAnnouncement::class);
    }

    /**
     * @param $jobSeekerId
     * @param array $jobAnnouncements
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function insertMultipleAlertedJobAnnouncementForJobSeeker($jobSeekerId, $jobAnnouncements = [])
    {
        if (count($jobAnnouncements)) {

            $values = [];
            foreach ($jobAnnouncements as $ja) {
                $jaId     = $ja['ja_id'];
                $values[] = "($jaId, $jobSeekerId, NOW(), NOW())";
            }

            $stmt = $this->_em->getConnection()->prepare('INSERT INTO alerted_job_announcement(job_announcement_id, job_seeker_id, created_at, updated_at) VALUES ' . implode(',', $values));
            $stmt->execute();
        }
    }

    /**
     * @param $cityId
     * @param null $from
     * @param null $to
     *
     * @return int|mixed|string
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getCountOfAlertedJobSeekers($cityId, $from = null, $to = null)
    {
        $qb = $this->createQueryBuilder('a')
                   ->select('COUNT(DISTINCT a.jobSeeker) AS cnt')
                   ->join('a.jobAnnouncement', 'ja')
                   ->join('ja.city', 'c')
                   ->where('c.id = :cityID')
                   ->setParameter('cityID', $cityId);

        if ($from && $to) {
            $qb->andWhere('a.createdAt BETWEEN :from AND :to')
               ->setParameter('from', $from)
               ->setParameter('to', $to);
        }

        return $qb->getQuery()->getSingleScalarResult();
    }

    public function getCountOfAlertedByJobTitle($jobTitleId,$statusId)
    {
        $qb = $this->createQueryBuilder('a')
                   ->select('COUNT(DISTINCT a.id) AS cnt')
                   ->join('a.jobAnnouncement', 'ja')
                   ->join('ja.jobTitle', 'jt')
                   ->leftJoin('ja.status', 's')
                   ->where('jt.id = :jobTitleId')
                   ->setParameter('jobTitleId', $jobTitleId)
                   ->andWhere('s.id = :statusId')
                   ->setParameter('statusId', $statusId);

        return $qb->getQuery()->getSingleScalarResult();
    }

}
