<?php

namespace App\Repository\City;

use App\Entity\City;
use App\Entity\City\Division;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Division|null find($id, $lockMode = null, $lockVersion = null)
 * @method Division|null findOneBy(array $criteria, array $orderBy = null)
 * @method Division[]    findAll()
 * @method Division[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DivisionRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Division::class);
    }

    public function getQueryBuilderByDepartmentAndCity($department, $city)
    {
        return $this->createQueryBuilder('division')
            ->andWhere('division.department = :department')
            ->setParameter('department', $department)
            ->andWhere('division.city = :city')
            ->setParameter('city', $city)
            ->addOrderBy('division.name', 'ASC')
            ;
    }

    public function getQueryForDivisionsForCity(City $city) {
        $qb = $this->createQueryBuilder('d')
            ->select('d.id, d.name, department.name as departmentName, COUNT(jt) as jobTitleCount')
            ->join('d.department', 'department')
            ->leftJoin('d.jobTitles', 'jt')
            ->where('d.city = :city')
            ->groupBy('d.id')
            ->orderBy('d.name')
            ->setParameter('city', $city);
        return $qb->getQuery();
    }


    // /**
    //  * @return Division[] Returns an array of Division objects
    //  */
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
    public function findOneBySomeField($value): ?Division
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
