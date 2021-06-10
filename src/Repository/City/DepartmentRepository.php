<?php

namespace App\Repository\City;

use App\Entity\City;
use App\Entity\City\Department;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Department|null find($id, $lockMode = null, $lockVersion = null)
 * @method Department|null findOneBy(array $criteria, array $orderBy = null)
 * @method Department[]    findAll()
 * @method Department[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DepartmentRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Department::class);
    }

    public function getArrayDepartmentIdAndNameForCity($city)
    {
        $departments = $this->createQueryBuilder('department')
            ->select('department.id, department.name')
            ->where('department.city = :city')
            ->setParameter('city', $city)
            ->orderBy('department.name')
            ->getQuery()
            ->getResult();

        $departmentArray = [];
        foreach ($departments as $department) {
            $departmentArray[$department['name']] = $department['id'];
        }
        return $departmentArray;
    }

//    /**
//     * @return Department[] Returns an array of Department objects
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
    public function findOneBySomeField($value): ?Department
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function getQueryBuilderToFindByCity($cityId)
    {
        return $this->createQueryBuilder('department')
            ->leftJoin('department.city', 'city')
            ->andWhere('city.id = :city_id')
            ->setParameter('city_id', $cityId)
            ->orderBy('department.name', 'ASC');
    }

    public function getQueryForDepartmentsForCity(City $city) {
        $qb = $this->createQueryBuilder('d')
            ->select('d.id, d.name, COUNT(jt) as jobTitleCount, COUNT(divisions) as divisionCount, d.hideOnProfilePage, d.orderByNumber')
            ->leftJoin('d.jobTitles', 'jt')
            ->leftJoin('d.divisions', 'divisions')
            ->where('d.city = :city')
            ->orderBy('d.orderByNumber', 'ASC')
            ->addOrderBy('d.name')
            ->groupBy('d.id')
            ->setParameter('city', $city);
        return $qb->getQuery();
    }

    public function getAllDepartmentsOrderByCity()
    {
        return $this->createQueryBuilder('department')
                    ->join('department.city', 'city')
                    ->orderBy('city.id', 'ASC')
                    ->addOrderBy('department.name', 'ASC')
                    ->getQuery()
                    ->getResult();
    }

    public function getDepartmentsByIds($departmentIds = null)
    {
        if ($departmentIds) {
            return $this->createQueryBuilder('department')
                        ->where('department IN (:departmentIds)')
                        ->setParameter('departmentIds', $departmentIds)
                        ->getQuery()
                        ->getResult();
        }

        return null;
    }

    public function getDepartmentOrderNumbers($cityId) {
        $stmt = $this->_em->getConnection()->prepare('SELECT id, name, order_by_number FROM department WHERE city_id = :cityId');
        $stmt->bindValue('cityId', $cityId);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
