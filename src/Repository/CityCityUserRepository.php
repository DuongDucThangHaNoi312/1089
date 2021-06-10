<?php

namespace App\Repository;

use App\Entity\City;
use App\Entity\CityCityUser;
use App\Entity\User\CityUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method CityCityUser|null find($id, $lockMode = null, $lockVersion = null)
 * @method CityCityUser|null findOneBy(array $criteria, array $orderBy = null)
 * @method CityCityUser[]    findAll()
 * @method CityCityUser[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CityCityUserRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CityCityUser::class);
    }


    public function getQueryForCityCityUsers(City $city, CityUser $cityUser) {
        $queryBuilder = $this->createQueryBuilder('city_city_user')
            ->select('city_user.id, city_user.email, city_user.firstname, city_user.lastname, city_user.enabled, city_user.lastLogin, city_user.confirmationToken')
            ->leftJoin('city_city_user.cityUser', 'city_user')
            ->andWhere('city_city_user.city = :city')
            ->andWhere('city_user != :cityUser')
            ->setParameter('cityUser', $cityUser)
            ->setParameter('city', $city);

        return $queryBuilder->getQuery();
    }

//    /**
//     * @return CityCityUser[] Returns an array of CityCityUser objects
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
    public function findOneBySomeField($value): ?CityCityUser
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
