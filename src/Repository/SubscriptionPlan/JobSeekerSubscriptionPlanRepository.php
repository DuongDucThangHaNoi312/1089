<?php

namespace App\Repository\SubscriptionPlan;

use App\Entity\SubscriptionPlan\JobSeekerSubscriptionPlan;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method JobSeekerSubscriptionPlan|null find($id, $lockMode = null, $lockVersion = null)
 * @method JobSeekerSubscriptionPlan|null findOneBy(array $criteria, array $orderBy = null)
 * @method JobSeekerSubscriptionPlan[]    findAll()
 * @method JobSeekerSubscriptionPlan[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class JobSeekerSubscriptionPlanRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, JobSeekerSubscriptionPlan::class);
    }

    public function getAllowedJobLevelIdsBySubscriptionPlan(JobSeekerSubscriptionPlan $jobSeekerSubscriptionPlan)
    {
        return $this->createQueryBuilder('jssp')
            ->select('allowed_job_levels.id as levelId')
            ->join('jssp.allowedJobLevels', 'allowed_job_levels')
            ->where('jssp = :jobSeekerSubscriptionPlan')
            ->setParameter('jobSeekerSubscriptionPlan', $jobSeekerSubscriptionPlan)
            ->getQuery()
            ->getResult('ColumnHydrator')
        ;
    }


    public function getAllSubscriptionsForJobSeeker()
    {
        return $this->createQueryBuilder('sp')
                    ->where('sp.isTrial = false')
                    ->andWhere('sp.isActive = true')
                    ->orderBy('sp.price')
                    ->getQuery()
                    ->getResult();
    }

}
