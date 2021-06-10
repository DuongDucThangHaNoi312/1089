<?php

namespace App\Repository;

use App\Entity\Url;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Url|null find($id, $lockMode = null, $lockVersion = null)
 * @method Url|null findOneBy(array $criteria, array $orderBy = null)
 * @method Url[]    findAll()
 * @method Url[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UrlRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Url::class);
    }

    public function findForCityIDs(array $ids)
    {
        return $this->createQueryBuilder('url')
            ->select('IDENTITY(url.city) as cityId, type.name as typeName, type.id as typeId, url.value, url.id')
            ->join('url.type', 'type')
            ->where('url.city IN (:ids)')
            ->orderBy('url.type', 'ASC')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult();
    }

    public function findForJobseekerJobTitleCard(array $types)
    {
        return $this->createQueryBuilder('url')
            ->select('IDENTITY(url.city) as cityId, type.name as typeName, type.id as typeId, url.value, url.id')
            ->join('url.type', 'type')
            ->where('type IN (:types)')
            ->setParameter('types', $types)
            ->getQuery()
            ->getResult();
    }

//    /**
//     * @return Url[] Returns an array of Url objects
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
    public function findOneBySomeField($value): ?Url
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
