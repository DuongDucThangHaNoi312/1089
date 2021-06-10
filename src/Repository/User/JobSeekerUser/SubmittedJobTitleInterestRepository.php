<?php

namespace App\Repository\User\JobSeekerUser;

use App\Entity\City;
use App\Entity\User;
use App\Entity\User\JobSeekerUser\SubmittedJobTitleInterest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method SubmittedJobTitleInterest|null find($id, $lockMode = null, $lockVersion = null)
 * @method SubmittedJobTitleInterest|null findOneBy(array $criteria, array $orderBy = null)
 * @method SubmittedJobTitleInterest[]    findAll()
 * @method SubmittedJobTitleInterest[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SubmittedJobTitleInterestRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, SubmittedJobTitleInterest::class);
    }

    public function getUserSubmittedInterestJobTitleIDs(User $user)
    {
        return $this->createQueryBuilder('submitted_job_title_interest')
            ->select('IDENTITY(submitted_job_title_interest.jobTitle)')
            ->where('submitted_job_title_interest.jobSeekerUser = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult('ColumnHydrator');
    }

    public function findAllByUser(User\JobSeekerUser $user, $maxResults = 3) {
        return $this->createQueryBuilder('submitted_job_title_interest')
            ->andWhere('submitted_job_title_interest.jobSeekerUser = :user')
            ->setParameter('user', $user)
            ->orderBy('submitted_job_title_interest.createdAt', 'ASC')
            ->getQuery()
            ->setMaxResults($maxResults)
            ->getResult()
            ;
    }

    public function getSumOfSubmittedInterestCityExperienceForCity(City $city)
    {

        $cityId = $city->getId();

        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('yoe', 'p');

        $sql = "SELECT SUM(p.yoe) as yoe FROM (
                    SELECT r1_.years_worked_in_city_government AS yoe 
                    FROM submitted_job_title_interest s2_ 
                    LEFT JOIN job_title j3_ ON s2_.job_title_id = j3_.id AND (j3_.deleted_at IS NULL) 
                    LEFT JOIN user u0_ ON s2_.job_seeker_user_id = u0_.id AND u0_.type IN ('job-seeker') 
                    LEFT JOIN resume r1_ ON u0_.id = r1_.job_seeker_id 
                    WHERE j3_.city_id = $cityId GROUP BY s2_.job_seeker_user_id) p";
        $qb  = $this->_em->createNativeQuery($sql, $rsm);

        $result = $qb->getSingleScalarResult();

        return $result ?? 0;
    }

    public function getSumOfSubmittedInterestForCity(City $city) {
        $qb = $this->createQueryBuilder('submitted_job_title_interest')
            ->select("SUM(DISTINCT(submitted_job_title_interest.id)) as sum_of_interest")
            ->leftJoin('submitted_job_title_interest.jobTitle', 'job_title')
            ->andWhere('job_title.isHidden = false')
            ->andWhere('job_title.city = :city')
            ->setParameter('city', $city);

            return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @param City $city
     * @param string $show
     * @param City\Department|null $department
     * @param null $jobTitle
     * @return \Doctrine\ORM\Query
     */
    public function getQuerySubmittedInterestForCity(City $city, $show = 'active', City\Department $department = null, $jobTitle = null)
    {
        $qb = $this->createQueryBuilder('si')
            ->select('si.id, si.createdAt, jtn.name as jobTitle, jsu.firstname as jsuFirstName, 
                jsu.lastname as jsuLastName, jsu.id as jsuId, jsu_city.name as jsuCity, jsu_county.name as jsuCounty, 
                jsu_state.name as jsuState, r.id as resumeId, r.yearsWorkedInCityGovernment, r.highestEducationLevel')
            ->join('si.jobSeekerUser', 'jsu')
            ->join('si.jobTitle', 'jt')
            ->join('jt.jobTitleName', 'jtn')
            ->join('jt.submittedJobTitleInterests', 'i')
            ->join('jsu.city', 'jsu_city')
            ->join('jsu.county', 'jsu_county')
            ->join('jsu.state', 'jsu_state')
            ->leftJoin('jsu.resume', 'r')
            ->where('jt.city = :city')
            ->groupBy('si')
            ->orderBy('jobTitle', 'ASC')
            ->setParameter('city', $city)
        ;
        if ($department) {
            $qb
                ->andWhere('jt.department = :department')
                ->setParameter('department', $department)
            ;
        }
        if ($jobTitle) {
            $qb
                ->andWhere('jt = :jobTitle')
                ->setParameter('jobTitle', $jobTitle)
            ;
        }
        switch ($show) {
            case 'active':
                $qb->andWhere('jt.isHidden is null or jt.isHidden = false');
                break;
            case 'hidden':
                $qb->andWhere('jt.isHidden = true');
                break;
            default:
        }

        return $qb->getQuery();
    }


//    /**
//     * @return SubmittedJobTitleInterest[] Returns an array of SubmittedJobTitleInterest objects
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
    public function findOneBySomeField($value): ?SubmittedJobTitleInterest
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function getSubmittedJobTitleInterestCountByCity($city): ?Int {
        return $this->createQueryBuilder('ijs')
            ->select('count(ijs.id)')
            ->leftJoin('ijs.jobTitle', 'jt')
            ->andWhere('jt.city = :city')
            ->setParameter('city', $city)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getSubmittedJobTitleInterestCountByJobTitle($jobTitleId): ?Int {
        return $this->createQueryBuilder('ijs')
            ->select('count(ijs.id)')
            ->leftJoin('ijs.jobTitle', 'jt')
            ->andWhere('jt.id = :jobTitleId')
            ->setParameter('jobTitleId', $jobTitleId)
            ->getQuery()
            ->getSingleScalarResult();
    }

}
