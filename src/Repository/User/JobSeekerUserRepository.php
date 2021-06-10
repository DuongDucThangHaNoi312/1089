<?php

namespace App\Repository\User;

use App\Entity\User;
use App\Entity\User\JobSeekerUser;
use App\Form\SaveSearchType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method JobSeekerUser|null find($id, $lockMode = null, $lockVersion = null)
 * @method JobSeekerUser|null findOneBy(array $criteria, array $orderBy = null)
 * @method JobSeekerUser[]    findAll()
 * @method JobSeekerUser[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class JobSeekerUserRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, JobSeekerUser::class);
    }

    /**
     * @param $offset
     * @param $limit
     *
     * @return mixed
     */
    public function getJobSeekersForAlertNotification($offset, $limit)
    {
        return $this->createQueryBuilder('js')
                    ->select("
                            js.id as job_seeker_id, js.email as job_seeker_email, js.firstname as job_seeker_firstname, js.receiveAlertsForSubmittedInterest as for_submitted_interest, js.receiveAlertsForJobsMatchingSavedSearchCriteria as for_jobs_matching_saved_search_criteria,
                            js.notificationPreferenceForSubmittedInterest as notification_preference_for_submitted_interest, js.notificationPreferenceForJobsMatchingSavedSearchCriteria as notification_preference_for_jobs_matching_saved_search_criteria,
                            GROUP_CONCAT(DISTINCT interested_county.id SEPARATOR ',') as counties,
                            interested_state.id as state, 
                            GROUP_CONCAT(DISTINCT job_level.id SEPARATOR ',') as allowed_job_levels,
                            GROUP_CONCAT(DISTINCT ijl.id SEPARATOR ', ') as interested_job_levels,
                            ijt.id as interested_job_type,
                            GROUP_CONCAT(DISTINCT ijc.id SEPARATOR ',') as interested_job_categories,
                            GROUP_CONCAT(DISTINCT ijtn.id SEPARATOR ',') as interested_job_title_names,
                            wfc.id as works_for_city,
                            max(aja.createdAt) as last_time_notified       
                    ")
                    ->leftJoin('js.county', 'user_county')
                    ->leftJoin('user_county.state', 'user_state')

                    ->leftJoin('js.subscription', 'sub')
                    ->leftJoin('sub.subscriptionPlan', 'plan')
                    ->leftJoin('plan.allowedJobLevels', 'job_level')

                    ->leftJoin('js.interestedCounties', 'interested_county')
                    ->leftJoin('interested_county.state', 'interested_state')

                    ->leftJoin('js.interestedJobLevels', 'ijl')
                    ->leftJoin('js.interestedJobType', 'ijt')
                    ->leftJoin('js.interestedJobCategories', 'ijc')
                    ->leftJoin('js.interestedJobTitleNames', 'ijtn')

                    ->leftJoin('js.worksForCity', 'wfc')
                    ->leftJoin('js.alertedJobAnnouncements', 'aja')

                    ->andWhere('js.enabled = 1')
                    ->andWhere("js.roles LIKE '%ROLE_JOBSEEKER%'")

                    // Make sure User has active Subscription. Subscriptions that have not been cancelled.
                    ->andWhere('sub.cancelledAt IS NULL')

                    ->groupBy('js.id')
                    ->setFirstResult($offset)
                    ->setMaxResults($limit)
                    ->getQuery()
                    ->getResult()
        ;

    }

    public function findByEmail($email)
    {
        return $this->getEntityManager()
            ->createQuery('SELECT u FROM App\Entity\User u WHERE u.email = :email')
            ->setParameters($email)
            ->getResult()
            ;
    }

    public function getInterestedCountiesAsIdArrayForUser(User $user)
    {
        return $this->createQueryBuilder('job_seeker_user')
            ->select('interested_counties.id')
            ->join('job_seeker_user.interestedCounties', 'interested_counties')
            ->where('job_seeker_user.id = :id')
            ->setParameter('id', $user->getId())
            ->getQuery()
            ->getResult('ColumnHydrator');
    }

    public function getInterestedJobTitlesAsIdArrayForUser(User $user)
    {
        return $this->createQueryBuilder('job_seeker_user')
            ->select('interested_job_title_names.id')
            ->join('job_seeker_user.interestedJobTitleNames', 'interested_job_title_names')
            ->where('job_seeker_user.id = :id')
            ->setParameter('id', $user->getId())
            ->getQuery()
            ->getResult('ColumnHydrator');
    }

    public function getInterestedJobTypesAsIdArrayForUser(User $user)
    {
        return $this->createQueryBuilder('job_seeker_user')
            ->select('interested_job_type.id')
            ->join('job_seeker_user.interestedJobType', 'interested_job_type')
            ->where('job_seeker_user.id = :id')
            ->setParameter('id', $user->getId())
            ->getQuery()
            ->getResult('ColumnHydrator');
    }

    public function getInterestedJobCategoriesAsIdArrayForUser(User $user)
    {
        return $this->createQueryBuilder('job_seeker_user')
            ->select('interested_job_categories.id')
            ->join('job_seeker_user.interestedJobCategories', 'interested_job_categories')
            ->where('job_seeker_user.id = :id')
            ->setParameter('id', $user->getId())
            ->getQuery()
            ->getResult('ColumnHydrator');
    }

    public function getInterestedJobLevelsAsIdArrayForUser(User $user)
    {
        return $this->createQueryBuilder('job_seeker_user')
            ->select('interested_job_level.id')
            ->join('job_seeker_user.interestedJobLevels', 'interested_job_levels')
            ->where('job_seeker_user.id = :id')
            ->setParameter('id', $user->getId())
            ->getQuery()
            ->getResult('ColumnHydrator');
    }

//    /**
//     * @return JobSeeker[] Returns an array of JobSeeker objects
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
    public function findOneBySomeField($value): ?JobSeeker
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
