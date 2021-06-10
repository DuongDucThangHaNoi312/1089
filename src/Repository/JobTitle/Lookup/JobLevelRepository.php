<?php

namespace App\Repository\JobTitle\Lookup;

use App\Entity\JobTitle\Lookup\JobLevel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method JobLevel|null find($id, $lockMode = null, $lockVersion = null)
 * @method JobLevel|null findOneBy(array $criteria, array $orderBy = null)
 * @method JobLevel[]    findAll()
 * @method JobLevel[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class JobLevelRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, JobLevel::class);
    }

//    /**
//     * @return JobLevel[] Returns an array of JobLevel objects
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
    public function findOneBySomeField($value): ?JobLevel
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
