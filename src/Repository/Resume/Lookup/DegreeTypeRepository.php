<?php

namespace App\Repository\Resume\Lookup;

use App\Entity\Resume\Lookup\DegreeType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method DegreeType|null find($id, $lockMode = null, $lockVersion = null)
 * @method DegreeType|null findOneBy(array $criteria, array $orderBy = null)
 * @method DegreeType[]    findAll()
 * @method DegreeType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DegreeTypeRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, DegreeType::class);
    }

//    /**
//     * @return DegreeType[] Returns an array of DegreeType objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?DegreeType
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
