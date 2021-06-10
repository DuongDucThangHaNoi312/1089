<?php

namespace App\Repository\City;

use App\Entity\City\County;
use App\Entity\User\JobSeekerUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method County|null find($id, $lockMode = null, $lockVersion = null)
 * @method County|null findOneBy(array $criteria, array $orderBy = null)
 * @method County[]    findAll()
 * @method County[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CountyRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, County::class);
    }

    public function getInterestedCountiesForUser(JobSeekerUser $user)
    {
        return $this->createQueryBuilder('c')
            ->join('');
    }

    public function findCountyAndState($term) {
        return $this->createQueryBuilder('county')
                    ->select('county.id as id, CONCAT(county.name, \', \', state.name) AS name')
                    ->leftJoin('county.state', 'state')
                    ->where('state.isActive = 1')
                    ->andWhere('county.isActive = 1')
                    ->andWhere('LOWER(county.name) LIKE LOWER(:term)  OR  LOWER(state.name) LIKE LOWER(:term)')
                    ->setParameter('term', '%'.$term.'%')
                    ->orderBy('county.name', 'ASC')
                    ->getQuery();
    }

    public function findByStateIsActive()
    {
        return $this->createQueryBuilder('c')
            ->join('c.state', 'state')
            ->where('state.isActive = true')
            ->orderBy('c.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findForCityID($id) {
        return $this->createQueryBuilder('county')
            ->select('county.id, county.name, state.name as stateName')
            ->join('county.cities', 'cities')
            ->join('county.state', 'state')
            ->where('cities.id = :id')
            ->andWhere('county.isActive = 1')
            ->setParameter('id', $id)
            ->getQuery()
            ->getResult()
            ;
    }

    public function findForCityIDs(array $ids) {
        return $this->createQueryBuilder('county')
            ->select('cities.id as cityId, county.id as countyId, county.name as countyName, county.slug as countySlug, state.id as stateId, state.name as stateName, state.slug as stateSlug')
            ->join('county.cities', 'cities')
            ->join('county.state', 'state')
            ->where('cities.id IN (:ids)')
            ->andWhere('county.isActive = 1 OR county.activateForCitySearch = 1')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult()
            ;
    }

    public function findByCities(array $ids) {
        return $this->createQueryBuilder('county')
            ->join('county.cities', 'cities')
            ->join('county.state', 'state')
            ->where('cities.id IN (:ids)')
            ->andWhere('county.isActive = 1')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult()
            ;
    }

    public function findCountiesByState(int $stateId)
    {
        return $this->createQueryBuilder('county')
                  ->where('county.state = :stateId')
                  ->setParameter('stateId', $stateId)
                  ->orderBy('county.name')
                  ->getQuery()
                  ->getResult();
    }

    public function findActiveCountiesByState($stateId, $searchCityLink = false)
    {
        $qb = $this->createQueryBuilder('county')
                    ->where('county.state = :stateId')
                    ->setParameter('stateId', $stateId)
                    ->orderBy('county.name');

        if ($searchCityLink) {
            $qb->andWhere('county.activateForCitySearch = 1 OR county.isActive = 1');
        }
        else {
            $qb->andWhere('county.isActive = 1');
        }

        return $qb->getQuery()->getResult();
    }

    public function findByCountyIDs(array $counties, $searchCityLink = false) {
        $qb = $this->createQueryBuilder('county')
            ->andWhere('county.id IN (:counties)')
            ->setParameter('counties', $counties)
            ->orderBy('county.name');

        if ($searchCityLink) {
            $qb->andWhere('county.activateForCitySearch = 1 OR county.isActive = 1');
        }
        else {
            $qb->andWhere('county.isActive = 1');
        }

        return $qb->getQuery()->getResult();
    }


    public function getQueryBuilderToOrderByName()
    {
        return $this->createQueryBuilder('county')
            ->orderBy('county.name', 'ASC');
    }

    public function findByWorksForCity($id) {
        return $this->createQueryBuilder('county')
                    ->join('county.cities', 'cities')
                    ->join('county.state', 'state')
                    ->where('cities.id = :id')
                    ->setParameter('id', $id)
                    ->getQuery()
                    ->getSingleResult();
    }

    public function getCountiesForSitemap()
    {
        return $this->createQueryBuilder('county')
                    ->where('county.activateForCitySearch = 1 OR county.isActive = 1')
                    ->getQuery()
                    ->getResult();
    }
}
