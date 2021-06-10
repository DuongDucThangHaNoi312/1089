<?php

namespace App\Repository\Resume;

use App\Entity\Resume\WorkHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method WorkHistory|null find($id, $lockMode = null, $lockVersion = null)
 * @method WorkHistory|null findOneBy(array $criteria, array $orderBy = null)
 * @method WorkHistory[]    findAll()
 * @method WorkHistory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WorkHistoryRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, WorkHistory::class);
    }

//    /**
//     * @return WorkHistory[] Returns an array of WorkHistory objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('w')
            ->andWhere('w.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('w.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?WorkHistory
    {
        return $this->createQueryBuilder('w')
            ->andWhere('w.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
