<?php

namespace App\Repository\City;

use App\Entity\City\CensusPopulation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method CensusPopulation|null find($id, $lockMode = null, $lockVersion = null)
 * @method CensusPopulation|null findOneBy(array $criteria, array $orderBy = null)
 * @method CensusPopulation[]    findAll()
 * @method CensusPopulation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CensusPopulationRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CensusPopulation::class);
    }

    public function getMaxPopulation(array $counties = null)
    {
        $qb = $this->createQueryBuilder('cp')
            ->select('MAX(cp.population) as maxPopulation')
        ;

        if ($counties) {
            $qb->join('cp.city', 'city')
                ->join('city.counties', 'counties')
                ->andWhere('counties.id IN (:counties)')
                ->setParameter('counties', $counties)
            ;
        }

        return $qb
            ->getQuery()
            ->getSingleScalarResult()
            ;
    }
}
