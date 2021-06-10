<?php

namespace App\Repository\User\JobSeekerUser;

use App\Entity\User;
use App\Entity\User\JobSeekerUser\SavedJobTitle;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method SavedJobTitle|null find($id, $lockMode = null, $lockVersion = null)
 * @method SavedJobTitle|null findOneBy(array $criteria, array $orderBy = null)
 * @method SavedJobTitle[]    findAll()
 * @method SavedJobTitle[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SavedJobTitleRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, SavedJobTitle::class);
    }

    public function getUserSavedJobTitleIDs(User $user)
    {
        return $this->createQueryBuilder('saved_job_title')
            ->select('IDENTITY(saved_job_title.jobTitle)')
            ->where('saved_job_title.jobSeekerUser = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult('ColumnHydrator');
    }

    public function findAllByUser(User\JobSeekerUser $user, $submittedInterestIds = [], $maxResults = 3) {
        $queryBuilder =  $this->createQueryBuilder('saved_job_title')
            ->andWhere('saved_job_title.jobSeekerUser = :user')
            ->setParameter('user', $user);

        if (count($submittedInterestIds) > 0) {
            $queryBuilder
                ->leftJoin('saved_job_title.jobTitle', 'job_title')
                ->andWhere('job_title NOT IN (:excludeJobTitles)')
                ->setParameter('excludeJobTitles', $submittedInterestIds);
        }

        return $queryBuilder
            ->orderBy('saved_job_title.createdAt', 'ASC')
            ->getQuery()
            ->setMaxResults($maxResults)
            ->getResult()
        ;
    }

//    /**
//     * @return SavedJobTitle[] Returns an array of SavedJobTitle objects
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
    public function findOneBySomeField($value): ?SavedJobTitle
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
