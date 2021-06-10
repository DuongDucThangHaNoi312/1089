<?php

namespace App\Repository\City;

use App\Entity\City;
use App\Entity\User;
use App\Entity\User\CityUser;
use App\Entity\User\JobSeekerUser;
use App\Service\ProfileDataSearchHelper;
use Doctrine\ORM\Query;
use App\Entity\City\JobTitle;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method JobTitle|null find($id, $lockMode = null, $lockVersion = null)
 * @method JobTitle|null findOneBy(array $criteria, array $orderBy = null)
 * @method JobTitle[]    findAll()
 * @method JobTitle[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class JobTitleRepository extends ServiceEntityRepository
{
    private $profileDataSearchHelper;
    /**
     * JobTitleRepository constructor.
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry, ProfileDataSearchHelper $profileDataSearchHelper)
    {
        parent::__construct($registry, JobTitle::class);
        $this->profileDataSearchHelper = $profileDataSearchHelper;
    }


    /**
     * @param $jobTitleName
     * @param $city
     * @param $department
     * @param $level
     * @param $type
     *
     * @return mixed
     */
    public function findDuplidateJobTitle($jobTitleName, $city, $department, $type) {
        $qb = $this->createQueryBuilder('jt')
                    ->join('jt.jobTitleName', 'jtn')
                    ->andWhere('jtn.name LIKE :jobTitleName')
                    ->setParameter('jobTitleName', $jobTitleName)
                    ->andWhere('jt.city = :city')
                    ->setParameter('city', $city)
                    ->andWhere('jt.department = :department')
                    ->setParameter('department', $department)
                    ->andWhere('jt.type = :type')
                    ->setParameter('type', $type)
        ;

        return $qb
            ->getQuery()
            ->setMaxResults(1)
            ->getResult();
    }


    /**
     * @param City $city
     * @param string $show
     * @param City\Department|null $department
     * @param null $jobTitle
     * @param bool $showJobTitleHasSubmittedInterest
     *
     * @return \Doctrine\ORM\Query
     */
    public function getQueryJobTitlesForCity(City $city, $show = 'active', City\Department $department = null, $jobTitle = null, $showJobTitleHasSubmittedInterest = false)
    {
        $qb = $this->createQueryBuilder('j')
                   ->select('j.id, jtn.name AS name, j.isVacant, j.isHidden, j.hiddenOn, j.deletedAt, t.name as type, l.name as level, l.slug as level_slug,
                GROUP_CONCAT(DISTINCT c.name SEPARATOR \', \') as category, d.id as department_id, d.name as department, 
                COUNT(DISTINCT i) as cntInterest, COUNT(ja) as cntJobAnnouncement, j.isClosedPromotional as isClosedPromotional,
                division.name as divisionName, CONCAT(mvb.firstname, \' \', mvb.lastname) as markedVacantByName'
                   )
                   ->join('j.type', 't')
                   ->join('j.jobTitleName', 'jtn')
                   ->leftJoin('j.level', 'l')
                   ->leftJoin('j.department', 'd')
                   ->leftJoin('j.division', 'division')
                   ->leftJoin('j.category', 'c');

        if ($showJobTitleHasSubmittedInterest) {
            $qb->join('j.submittedJobTitleInterests', 'i');
        } else {
            $qb->leftJoin('j.submittedJobTitleInterests', 'i');
        }

        $qb->leftJoin('j.jobAnnouncements', 'ja')
           ->leftJoin('j.markedVacantBy', 'mvb')
           ->where('j.city = :city')
           ->groupBy('j.id')
           ->setParameter('city', $city);

        if ($department) {
            $qb
                ->andWhere('j.department = :department')
                ->setParameter('department', $department)
            ;
        }
        if ($jobTitle) {
            $qb
                ->andWhere('jtn.name LIKE :jobTitle')
                ->setParameter('jobTitle', '%'.$jobTitle.'%')
            ;
        }
        switch ($show) {
            case 'active':
                $qb->andWhere('j.isHidden is null or j.isHidden = false');
                break;
            case 'hidden':
                $qb->andWhere('j.isHidden = true');
                break;
            case 'deleted':
                $qb->andWhere('j.deletedAt IS NOT NULL');
            default:
        }

        return $qb->getQuery();
    }

    /**
     * @param CityUser $user
     * @param City $city
     * @param int $maxResults
     * @return mixed
     */
    public function findForCityUserDashboard(City $city, $maxResults = 4) {
        $qb = $this->createQueryBuilder('job_title')
            ->select('job_title.id as job_title_id, jtn.name as job_title_name, department.name as department_name, department.id as department_id, COUNT(submitted_job_title_interests) as interest')
            ->join('job_title.jobTitleName', 'jtn')
            ->leftJoin('job_title.department', 'department')
            ->leftJoin('job_title.submittedJobTitleInterests', 'submitted_job_title_interests')
            ->andWhere('job_title.isHidden = false')
            ->andWhere('job_title.city = :city')
            ->setParameter('city', $city)
            ->orderBy('job_title.name', 'ASC')
            ->orderBy('interest', 'DESC')
            ->groupBy('job_title.id');

        return $qb
            ->getQuery()
            ->setMaxResults($maxResults)
            ->getResult();
    }

    /**
     * @param $city
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getJobCountByCity($city){
        return $this->createQueryBuilder('j')
            ->select('count(j.id)')
            ->andWhere('j.city = :city')
            ->setParameter('city', $city)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findJobTitlesForCityDepartment($city, $department = null) {
        $queryBuilder = $this->createQueryBuilder('jt')
            ->join('jt.city', 'city')
            ->where('city.id = :city')
            ->join('jt.jobTitleName', 'jtn')
            ->setParameter('city', $city)
            ->orderBy('jtn.name');

        if ($department) {
            $queryBuilder->join('jt.department', 'jtd')
                ->andWhere('jtd.id = :department')
                ->setParameter('department', $department);
        }

        return $queryBuilder->getQuery()->getResult();
    }

    public function findJobTitlesForDepartment($department) {
        $queryBuilder = $this->createQueryBuilder('jt')
            ->join('jt.jobTitleName', 'jtn')
            ->orderBy('jtn.name');

        if ($department) {
            $queryBuilder->join('jt.department', 'jtd')
                ->andWhere('jtd.id = :department')
                ->setParameter('department', $department);
        }

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param $city
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getTotalJobTitlesAcceptingInterest($city) {
        return $this->createQueryBuilder('j')
            ->select('count(j.id)')
            ->andWhere('j.isHidden = false')
            ->andWhere('j.city = :city')
            ->setParameter('city', $city)
            ->getQuery()
            ->setHydrationMode(Query::HYDRATE_SINGLE_SCALAR)
            ->getSingleScalarResult();
    }

    /**
     * @param $queryBuilder
     * @param $alias
     * @param $field
     * @param $value
     * @return bool
     */
    public function filterJobTitleByDepartment(QueryBuilder $queryBuilder, $alias, $field, $value)
    {
        if($value['value']) {
            if($value['value'] == 'no_department') {
                $queryBuilder->andWhere($alias.'.department IS NULL');
            } else {
                $queryBuilder->leftJoin(sprintf('%s.department', $alias), 'department');
                $queryBuilder->andWhere('department = :department');
                $queryBuilder->setParameter('department', $value['value']);
            }
            return true;
        }
    }

    /**
     * @param JobSeekerUser $user
     * @param $searchData
     *
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getJobCountMatchingProfile(JobSeekerUser $user, $searchData) {
        $filterQueryBuilder = $this->getQueryBuilderWithSearchFilterData($searchData, false)->select('jobTitle.id');
        $mainQueryBuilder = $this->createQueryBuilder('jt')
            ->select('COUNT(jt.id)')
            ->andWhere('jt.id IN ('.$filterQueryBuilder->getDQL().')');

        // Need to redefine the parameters from the searchData Query builder since we just included the DQL within the main Query
        /** @var Query\Parameter $v */
        foreach ($filterQueryBuilder->getParameters() as $v) {
            $mainQueryBuilder->setParameter($v->getName(), $v->getValue());
        }

        return $mainQueryBuilder
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param null $searchData
     * @param bool $selectiveFields
     * @return Query
     */
    public function getQueryWithSearchFilterData($searchData = null) {
        return $this->getQueryBuilderWithSearchFilterData($searchData, true)->getQuery();
    }

    public function getCountWithSearchFilterData($searchData = null) {
        return $this->getQueryBuilderWithSearchFilterData($searchData, false, true)->getQuery()->getSingleScalarResult();
    }

    /**
     * @param null $searchData
     * @param bool $selectiveFields
     *
     * @return QueryBuilder
     */
    public function getQueryBuilderWithSearchFilterData($searchData = null, $selectiveFields = true, $count = false) {
        $qb = $this->createQueryBuilder('jobTitle');
        // Return the selected fields are return the entire object.
        if ($selectiveFields) {
            $qb
                ->select('
                jobTitle.id as jobTitleId,
                jobTitle.isClosedPromotional as isClosedPromotional,
                jtn.name as jobTitleName,
                city.id as cityId,
                city.name as cityName,
                city.slug as citySlug,
                city.timezone as cityTimezone,
                city.cgjPostsJobs as cityCgjPostsJobs,
                department.name as departmentName,
                division.name as divisionName,
                type.name as typeName,
                GROUP_CONCAT(DISTINCT category.name SEPARATOR \', \') as categoryName,
                level.name as levelName,
                level.id as levelId, 
                subscription.expiresAt as subscriptionExpiresAt,
                city.currentStars as stars
                ');
        }

        $qb
            ->join('jobTitle.jobTitleName', 'jtn')
            ->join('jobTitle.department', 'department')
            ->join('jobTitle.level', 'level')
            ->join('jobTitle.city', 'city')
            ->join('city.counties', 'counties')
            ->leftJoin('jobTitle.division', 'division')
            ->leftJoin('city.subscription', 'subscription')
            ->join('jobTitle.type', 'type')
            ->leftJoin('jobTitle.category', 'category')
            ->where('jobTitle.isHidden = false')
            ->andWhere('city.isSuspended = false')
            ->andWhere('counties.isActive = 1')
            ->orderBy('jtn.name, city.name', 'ASC')
        ;

        if ($count) {
            $qb->select('COUNT(DISTINCT jobTitle.id)');
        }
        else {
            $qb->groupBy('jobTitle.id');
        }

        $this->profileDataSearchHelper->filterQueryBuilderByProfileData($qb, 'jobTitle', '', 'city', 'jtn', $searchData);

        return $qb;
    }

    public function findByState($state)
    {
        return $this->createQueryBuilder('j')
            ->join('j.city', 'city')
            ->join('city.counties', 'counties')
            ->join('counties.state', 'state')
            ->where('state = :state')
            ->andWhere('city.isSuspended = false')
            ->andWhere('counties.isActive = 1')
            ->setParameter('state', $state)
            ->getQuery()
            ->getResult();
    }

    public function findByCounties(array $counties)
    {
        return $this->createQueryBuilder('j')
            ->join('j.city', 'city')
            ->join('city.counties', 'counties')
            ->where('counties IN (:counties)')
            ->andWhere('counties.isActive = 1')
            ->setParameter('counties', $counties)
            ->getQuery()
            ->getResult();
    }

    public function findByCities(array $cities) {
        return $this->createQueryBuilder('j')
            ->join('j.city', 'city')
            ->join('j.jobTitleName', 'jtn')
            ->andWhere('j.isHidden = false')
            ->andWhere('j.city IN (:cities)')
            ->andWhere('city.isSuspended = false')
            ->setParameter('cities', $cities)
            ->orderBy('jtn.name', 'ASC')
            ->getQuery()
            ->getResult();
    }


    /**
     * @param $type
     * @param $offset
     * @param $limit
     *
     * @return mixed
     */
    public function findJobTitleMLData($type, $offset, $limit)
    {
        $qb = $this->createQueryBuilder('j')
            ->join('j.jobTitleName', 'jtn')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->orderBy('j.id');

        if ($type == 'level') {
            $qb->select('DISTINCT jtn.name as sample, l.name as target')
               ->join('j.level', 'l');
        }
        elseif ($type == 'category') {
            $qb->select("DISTINCT CONCAT(jtn.name, ' ', d.name)  as sample, c.name as target")
                ->join('j.category', 'c')
                ->join('j.department', 'd');
        }

        return $qb->getQuery()->getResult(Query::HYDRATE_ARRAY);
    }

}
