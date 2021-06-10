<?php

namespace App\Repository\User;

use App\Entity\User\JobSeekerUser;
use App\Entity\User\UserLogin;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method UserLogin|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserLogin|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserLogin[]    findAll()
 * @method UserLogin[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserLoginRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, UserLogin::class);
    }

    public function getTotalLogin(JobSeekerUser $user) {
        return $this->createQueryBuilder('u')
                    ->select('COUNT(u.id) as totalLogins')
                    ->join('u.user', 'user')
                    ->where('u.loginTime BETWEEN :lastDay AND :now')
                    ->andWhere('user.id = :userId')
                    ->setParameters(['lastDay' => new \DateTime('-39 days'), 'now' => new \DateTime('now'), 'userId' => $user->getId()])
                    ->getQuery()
                    ->getOneOrNullResult();
    }

    // /**
    //  * @return UserLogin[] Returns an array of UserLogin objects
    //  */
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
    public function findOneBySomeField($value): ?UserLogin
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
