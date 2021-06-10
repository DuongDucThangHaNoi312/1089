<?php

namespace App\Repository\JobTitle\Lookup;

use App\Entity\JobTitle\Lookup\JobTitleName;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method JobTitleName|null find($id, $lockMode = null, $lockVersion = null)
 * @method JobTitleName|null findOneBy(array $criteria, array $orderBy = null)
 * @method JobTitleName[]    findAll()
 * @method JobTitleName[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class JobTitleNameRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, JobTitleName::class);
    }

    public function findCityByTerm($term, $stateId = null, $countyId = null)
    {

        $qb = $this->createQueryBuilder('jtn')
                   ->select('jtn.id as id, jtn.name AS text')
                   ->join('jtn.jobTitles', 'j')
                   ->join('j.city', 'city')
                   ->join('city.counties', 'counties')
                   ->join('counties.state', 'state')
                   ->where('city.isSuspended = false');
        if ($stateId) {
            $qb->andWhere('state.id = :stateId')
               ->setParameter('stateId', $stateId);
        }
        if ($countyId) {
            $qb->andWhere('counties.id = :countyId')
               ->setParameter('countyId', $countyId);
        }
        $qb->andWhere('j.isHidden = false')
           ->andWhere('LOWER(jtn.name) LIKE LOWER(:term)')
           ->setParameter('term', '%' . $term . '%')
           ->orderBy('jtn.name')
           ->distinct();

        return $qb->getQuery();
    }

    public function findAllVisible()
    {
        return $this->createQueryBuilder('jtn')
            ->join('jtn.jobTitles', 'j')
            ->join('j.city', 'city')
            ->join('city.counties', 'counties')
            ->join('counties.state', 'state')
            ->where('city.isSuspended = false')
            ->andWhere('j.isHidden = false')
            ->orderBy('jtn.name')
            ->distinct()
            ->getQuery()
            ->getResult();
    }

    public function findByState($state)
    {
        return $this->createQueryBuilder('jtn')
            ->join('jtn.jobTitles', 'j')
            ->join('j.city', 'city')
            ->join('city.counties', 'counties')
            ->join('counties.state', 'state')
            ->where('state = :state')
            ->andWhere('city.isSuspended = false')
            ->andWhere('j.isHidden = false')
            ->andWhere('counties.isActive = 1')
            ->orderBy('jtn.name')
            ->distinct()
            ->setParameter('state', $state)
            ->getQuery()
            ->getResult();
    }

    public function findByCounties(array $counties)
    {
        return $this->createQueryBuilder('jtn')
            ->join('jtn.jobTitles', 'j')
            ->join('j.city', 'city')
            ->join('city.counties', 'counties')
            ->where('counties IN (:counties)')
            ->andWhere('city.isSuspended = false')
            ->andWhere('j.isHidden = false')
            ->orderBy('jtn.name')
            ->distinct()
            ->setParameter('counties', $counties)
            ->getQuery()
            ->getResult();
    }

    public function findByCities(array $cities) {
        return $this->createQueryBuilder('jtn')
            ->join('jtn.jobTitles', 'j')
            ->join('j.city', 'city')
            ->andWhere('j.city IN (:cities)')
            ->andWhere('city.isSuspended = false')
            ->andWhere('j.isHidden = false')
            ->orderBy('jtn.name')
            ->distinct()
            ->setParameter('cities', $cities)
            ->getQuery()
            ->getResult();
    }

    public function findByCountiesAndJobLevel(array $counties, array $jobLevels)
    {
        return $this->createQueryBuilder('jtn')
                    ->join('jtn.jobTitles', 'j')
                    ->join('j.city', 'city')
                    ->join('city.counties', 'counties')
                    ->leftJoin('j.level', 'levels')
                    ->where('counties IN (:counties)')
                    ->andWhere('levels IN (:levels)')
                    ->andWhere('j.isHidden = false')
                    ->andWhere('counties.isActive = 1')
                    ->orderBy('jtn.name')
                    ->distinct()
                    ->setParameter('levels', $jobLevels)
                    ->setParameter('counties', $counties)
                    ->getQuery()
                    ->getResult();
    }

}
