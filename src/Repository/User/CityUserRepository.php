<?php

namespace App\Repository\User;

use App\Entity\User;
use App\Entity\User\CityUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method CityUser|null find($id, $lockMode = null, $lockVersion = null)
 * @method CityUser|null findOneBy(array $criteria, array $orderBy = null)
 * @method CityUser[]    findAll()
 * @method CityUser[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CityUserRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CityUser::class);
    }

    public function findByEmail($email)
    {
        return $this->getEntityManager()
            ->createQuery('SELECT u FROM App\Entity\User u WHERE u.email = :email')
            ->setParameters($email)
            ->getResult()
            ;
    }

    public function findCityUserForMonthlyReport($offset, $limit)
    {
        return $this->createQueryBuilder('cu')
                    ->setFirstResult($offset)
                    ->setMaxResults($limit)
                    ->getQuery()
                    ->getResult();
    }
}
