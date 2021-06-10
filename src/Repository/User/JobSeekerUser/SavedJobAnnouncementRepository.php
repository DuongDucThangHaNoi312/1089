<?php

namespace App\Repository\User\JobSeekerUser;

use App\Entity\JobAnnouncement;
use App\Entity\User\JobSeekerUser;
use App\Entity\User\JobSeekerUser\SavedJobAnnouncement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method SavedJobAnnouncement|null find($id, $lockMode = null, $lockVersion = null)
 * @method SavedJobAnnouncement|null findOneBy(array $criteria, array $orderBy = null)
 * @method SavedJobAnnouncement[]    findAll()
 * @method SavedJobAnnouncement[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SavedJobAnnouncementRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, SavedJobAnnouncement::class);
    }


    public function findAllByUser(JobSeekerUser $user, $maxResults = 3) {
        return $this->createQueryBuilder('saved_job_announcement')
            ->join('saved_job_announcement.jobAnnouncement', 'ja')
            ->andWhere('ja.status = :status')
            ->setParameter('status', JobAnnouncement::STATUS_ACTIVE)
            ->andWhere('saved_job_announcement.jobSeekerUser = :user')
            ->orderBy('saved_job_announcement.createdAt', 'ASC')
            ->setParameter('user', $user)
            ->getQuery()
            ->setMaxResults($maxResults)
            ->getResult()
        ;
    }

    public function findByUserId($value)
    {
        return $this->createQueryBuilder('s')
                    ->andWhere('s.jobSeekerUser = :userId')
                    ->setParameter('userId', $value)
                    ->getQuery()
                    ->getResult();
    }

    public function countByUser(JobSeekerUser $user) {
        return $this->createQueryBuilder('saved_job_announcement')
                    ->select('count(saved_job_announcement)')
                    ->join('saved_job_announcement.jobAnnouncement', 'ja')
                    ->andWhere('ja.status = :status')
                    ->setParameter('status', JobAnnouncement::STATUS_ACTIVE)
                    ->andWhere('saved_job_announcement.jobSeekerUser = :user')
                    ->setParameter('user', $user)
                    ->getQuery()
                    ->getSingleScalarResult()
            ;
    }

//    /**
//     * @return SavedJobAnnouncement[] Returns an array of SavedJobAnnouncement objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?SavedJobAnnouncement
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
