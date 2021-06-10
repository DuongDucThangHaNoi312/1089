<?php

namespace App\Repository;

use App\Entity\City;
use App\Entity\JobAnnouncement;
use App\Entity\User\JobSeekerUser;
use App\Hydrators\ColumnHydrator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method City|null find($id, $lockMode = null, $lockVersion = null)
 * @method City|null findOneBy(array $criteria, array $orderBy = null)
 * @method City[]    findAll()
 * @method City[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CityRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, City::class);
    }

    public function findByState($state)
    {
        return $this->createQueryBuilder('c')
            ->join('c.counties', 'counties')
            ->join('counties.state', 'state')
            ->where('state = :state')
            ->andWhere('c.isSuspended = false')
            ->andWhere('counties.isActive = 1')
            ->andWhere('c.prefix IS NOT NULL')
            ->orderBy('c.name')
            ->setParameter('state', $state)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param $cityID
     * @param null $from
     * @param null $to
     *
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getCountOfUsersWhoSubmittedInterest($cityID, $from = null, $to = null)
    {
        $qb = $this->createQueryBuilder('c')
                   ->select('COUNT(DISTINCT si.jobSeekerUser) AS cntInterestedUser')
                   ->join('c.jobTitles', 'jt')
                   ->join('jt.submittedJobTitleInterests', 'si')
                   ->where('c.id = :cityID')
                   ->setParameter('cityID', $cityID);

        if ($from && $to) {
            $qb->andWhere('si.createdAt BETWEEN :from AND :to')
               ->setParameter('from', $from)
               ->setParameter('to', $to);
        }

        return $qb->getQuery()->getSingleScalarResult();
    }

    public function getMaxEmployees(array $counties = null)
    {
        $qb = $this->createQueryBuilder('c')
            ->select('MAX(c.countFTE) as maxEmployees')
        ;

        if ($counties) {
            $qb->join('c.counties', 'counties')
                ->andWhere('counties.id IN (:counties)')
                ->setParameter('counties', $counties)
            ;
        }

        return $qb
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function getCityIdsByCounty(City\County $county)
    {
        return $this->createQueryBuilder('c')
            ->select('c.id')
            ->join('c.counties', 'counties')
            ->where('counties.id = :county')
            ->setParameter('county', $county)
            ->getQuery()
            ->getResult('ColumnHydrator')
        ;
    }

    public function getCityIdBlockedByJobSeeker(JobSeekerUser $user)
    {
        return $this->createQueryBuilder('c')
            ->select('c.id as cityId')
            ->join('c.blockedResumes', 'r')
            ->where('r.jobSeeker = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult('ColumnHydrator')
        ;
    }

    public function getTotalSubmittedInterest(City $city): int {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.jobTitles', 'job_title')
            ->leftJoin('job_title.submittedJobTitleInterests', 'sjti')
            ->select('COUNT(sjti) as submitted_interest_count')
            ->andWhere('job_title.city = :city')
            ->setParameter('city', $city)
            ->getQuery()
            ->setHydrationMode(Query::HYDRATE_SINGLE_SCALAR)
            ->getSingleScalarResult();
    }

    public function findAllInEnabledStates() {
        return $this->createQueryBuilder('city')
            ->leftJoin('city.counties', 'county')
            ->leftJoin('county.state', 'state')
            ->andWhere('state.isActive = 1')
            ->andWhere('county.isActive = 1')
            ->orderBy('city.name', 'ASC')
            ->getQuery()
            ->getResult();
    }


    public function findOneByName($name) {
        return $this->createQueryBuilder('city')
            ->andWhere('city.name = :name')
            ->setParameter('name', $name)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findForCitySearch() {
        return $this->createQueryBuilder('city')
            ->select('city.id, city.name, CONCAT(city.name, \', \', county.name, \', \', state.name) AS cityString')
            ->leftJoin('city.counties', 'county')
            ->leftJoin('county.state', 'state')
            ->andWhere('state.isActive = 1')
            ->andWhere('county.isActive = 1')
            ->orderBy('city.name', 'ASC')
            ->getQuery()
            ->getArrayResult();

    }

    /**
     * Get all Cities that are active in a State
     * @param int $county
     * @return mixed
     */
    public function findAllByCounty(int $county) {
        return $this->createQueryBuilder('city')
            ->leftJoin('city.counties', 'county')
            ->leftJoin('county.state', 'state')
            ->andWhere('state.isActive = 1')
            ->andWhere('city.prefix IS NOT NULL')
            ->andWhere('county.id = :county')
            ->andWhere('city.isSuspended = false')
            ->setParameter('county', $county)
            ->getQuery()
            ->getResult()
            ;
    }

    public function findCitiesByCounties(array $counties, $isCityLinkSearch = false)
    {
        $qb = $this->createQueryBuilder('city')
                   ->leftJoin('city.counties', 'county')
                   ->leftJoin('county.state', 'state')
                   ->andWhere('state.isActive = 1')
                   ->andWhere('county.id IN (:counties)')
                   ->andWhere('city.isSuspended = false')
                   ->andWhere('city.prefix is not null')
                   ->orderBy('city.name')
                   ->setParameter('counties', $counties);

        if ($isCityLinkSearch) {
            $qb->andWhere('county.activateForCitySearch = 1 OR county.isActive = 1');
        }
        else {
            $qb->andWhere('county.isActive = 1');
        }

        return $qb->getQuery()
                  ->getResult();
    }

    public function findForCityIDs(array $cities) {
        return $this->createQueryBuilder('city')
            ->andWhere('city.id IN (:cities)')
            ->setParameter('cities', $cities)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param $name
     * @param $county
     * @return City|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneByCounty($name, $county): ?City
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.counties', 'county')
            ->andWhere('c.name = :name')
            ->andWhere('county.id = :county')
            ->setParameter('name', $name)
            ->setParameter('county', $county)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }

    public function findCityByTerm($term) {
        return $this->createQueryBuilder('city')
            ->select('city.id as id, CONCAT(city.name, \', \', county.name, \', \', state.name) AS name')
            ->leftJoin('city.counties', 'county')
            ->leftJoin('county.state', 'state')
            ->where('state.isActive = 1')
            ->andWhere('county.isActive = 1')
            ->andWhere('LOWER(city.name) LIKE LOWER(:term)  OR  LOWER(county.name) LIKE LOWER(:term)  OR  LOWER(state.name) LIKE LOWER(:term)')
            ->andWhere('city.prefix IS NOT NULL')
            ->setParameter('term', '%'.$term.'%')
            ->orderBy('name', 'ASC')
            ->getQuery();
    }

    public function findCityCountyAndState(string $term) {
        return $this->createQueryBuilder('city')
            ->select('CONCAT(city.id, \'_\', county.id) as id, city.name, CONCAT(city.name, \', \', county.name, \', \', state.name) AS cityString')
            ->leftJoin('city.counties', 'county')
            ->leftJoin('county.state', 'state')
            ->where('state.isActive = 1')
            ->andWhere('LOWER(city.name) LIKE LOWER(:term)  OR  LOWER(county.name) LIKE LOWER(:term)  OR  LOWER(state.name) LIKE LOWER(:term)')
            ->setParameter('term', '%'.$term.'%')
            ->orderBy('city.name', 'ASC')
            ->getQuery();
    }

    public function findIncorporatedCityCountyAndState(string $term) {
        return $this->createQueryBuilder('city')
            ->select('CONCAT(city.id, \'_\', county.id) as id, city.name, CONCAT(city.name, \', \', county.name, \', \', state.name) AS cityString')
            ->leftJoin('city.counties', 'county')
            ->leftJoin('county.state', 'state')
            ->where('state.isActive = 1')
            ->andWhere('county.isActive = 1')
            ->andWhere('LOWER(city.name) LIKE LOWER(:term)  OR  LOWER(county.name) LIKE LOWER(:term)  OR  LOWER(state.name) LIKE LOWER(:term)')
            ->andWhere('city.prefix IS NOT NULL')
            ->setParameter('term', '%'.$term.'%')
            ->orderBy('city.name', 'ASC')
            ->getQuery();
    }

    public function getQueryWithSearchFilterData($searchData = null) {

        $qb = $this->createQueryBuilder('city')
            ->select('
            city.id, 
            city.name,
            city.slug,
            city.countFTE,
            city.timezone,
            subscription.expiresAt as expiresAt,
            subscription.cancelledAt as cancelledAt,
            (SELECT cp.population FROM App:City\CensusPopulation cp WHERE cp.city = city AND cp.year = 
                (SELECT MAX(cp2.year) FROM App:City\CensusPopulation cp2)
            ) AS population,
            (SELECT COUNT(jt1) FROM App:City\JobTitle jt1 WHERE jt1.city = city AND jt1.isHidden = false
            ) AS cntJobTitles,
            (SELECT COUNT(ja) FROM App:JobAnnouncement ja JOIN App:City\JobTitle jt2 WHERE ja.jobTitle = jt2 
               AND jt2.city = city AND jt2.isHidden = false AND ja.status = '.JobAnnouncement::STATUS_ACTIVE.'
            ) AS cntJobAnnouncements
            ')
            ->join('city.counties', 'counties')
            ->join('counties.state', 'state')
            ->leftJoin('city.subscription', 'subscription')
            ->andWhere('city.isSuspended = false')
            ->andWhere('state.isActive = 1')
            ->andWhere('counties.activateForCitySearch = 1 OR counties.isActive = 1')
            ->andWhere('city.prefix IS NOT NULL')
            ->orderBy('city.name', 'ASC')
            ->groupBy('city.id')

        ;

        if (isset($searchData['saved']) && $searchData['saved']) {
            $qb->join('city.savedCities', 'savedCities')
                ->join('savedCities.user', 'user')
                ->andWhere('user = :user')
                ->setParameter('user', $searchData['user']);
        }

        if (isset($searchData['state']) || isset($searchData['counties'])) {
            if (isset($searchData['state']) && $searchData['state']) {
                $qb->andWhere('counties.state = :stateId')
                    ->setParameter('stateId', $searchData['state']);
            }
            if (isset($searchData['counties']) && count($searchData['counties'])) {
                $qb->andWhere('counties IN (:countyIds)')
                    ->setParameter('countyIds', $searchData['counties']);
            }
        }

        if (isset($searchData['population']) && $searchData['population']) {
            $qb->leftJoin('city.censusPopulations', 'census_populations');

            $population = array_map('trim', explode(';', $searchData['population']));

            if (count($population) > 1) {
                $populationMin = $population[0];
                $populationMax = end($population);
            } elseif (count($population) == 1) {
                $populationMin = $population[0];
            }

            if (isset($populationMin) && $populationMin) {
                $qb->andWhere('census_populations.population >= :populationMin')
                   ->andWhere('census_populations.year = (SELECT MAX(cp3.year) FROM App:City\CensusPopulation cp3)')
                   ->setParameter('populationMin', $populationMin);
            }
            if (isset($populationMax) && $populationMax) {
                $qb->andWhere('census_populations.population <= :populationMax')
                   ->setParameter('populationMax', $populationMax);
            }
        }

        if (isset($searchData['cities']) && count($searchData['cities'])) {
            $qb->andWhere('city IN (:cityIds)')
                ->setParameter('cityIds', $searchData['cities']);
        }
        if (isset($searchData['jobCategories']) && count($searchData['jobCategories'])) {
            $qb->join('city.jobTitles', 'city_job_titles')
                ->join('city_job_titles.category', 'job_categories')
                ->andWhere('job_categories IN (:jobCategoryIds)')
                ->setParameter('jobCategoryIds', $searchData['jobCategories']);
        }
        
        if (isset($searchData['jobTitleNames']) && count($searchData['jobTitleNames'])) {
            $qb->join('city.jobTitles', 'job_titles');
            $qb->join('job_titles.jobTitleName', 'jtn');
            $qb->andWhere('jtn IN (:jobTitleIds)')
                ->setParameter('jobTitleIds', $searchData['jobTitleNames']);
        }

        if (isset($searchData['employees']) && $searchData['employees']) {
            $employees = array_map('trim', explode(';', $searchData['employees']));

            if (count($employees) > 1) {
                $employeesMin = $employees[0];
                $employeesMax = end($employees);
            } elseif (count($employees) == 1) {
                $employeesMin = $employees[0];
            }

            if (isset($employeesMin) && $employeesMin) {
                $qb->andWhere('city.countFTE is not NULL AND city.countFTE >= :employeeMin')
                   ->setParameter('employeeMin', $employeesMin);
            }
            if (isset($employeesMax) && $employeesMax) {
                $qb->andWhere('city.countFTE is not NULL AND city.countFTE <= :employeeMax')
                   ->setParameter('employeeMax', $employeesMax);
            }
        }

        return $qb->getQuery();
    }

    public function getCitiesForSitemap()
    {
        return $this->createQueryBuilder('city')
                    ->select('city.slug as citySlug, counties.slug as countySlug, state.slug as stateSlug')
                    ->join('city.counties', 'counties')
                    ->join('counties.state', 'state')
                    ->where('state.isActive = 1 AND (counties.isActive = 1 OR counties.activateForCitySearch = 1) AND city.isSuspended = 0')
                    ->andWhere('city.prefix is not null')
                    ->getQuery()
                    ->getScalarResult();
    }
}
