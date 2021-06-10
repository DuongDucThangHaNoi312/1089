<?php

namespace App\Repository;

use App\Entity\CMSBlock;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method CMSBlock|null find($id, $lockMode = null, $lockVersion = null)
 * @method CMSBlock|null findOneBy(array $criteria, array $orderBy = null)
 * @method CMSBlock[]    findAll()
 * @method CMSBlock[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CMSBlockRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CMSBlock::class);
    }

//    /**
//     * @return CMSBlock[] Returns an array of CMSBlock objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?CMSBlock
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
