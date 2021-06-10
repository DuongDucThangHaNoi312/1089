<?php

namespace App\Repository\Lookup;

use App\Entity\Lookup\UrlType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method UrlType|null find($id, $lockMode = null, $lockVersion = null)
 * @method UrlType|null findOneBy(array $criteria, array $orderBy = null)
 * @method UrlType[]    findAll()
 * @method UrlType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UrlTypeRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, UrlType::class);
    }

//    /**
//     * @return UrlType[] Returns an array of UrlType objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?UrlType
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
