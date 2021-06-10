<?php

namespace App\Repository\User\JobSeekerUser;

use App\Entity\User\JobSeekerUser\DismissedJobTitle;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method DismissedJobTitle|null find($id, $lockMode = null, $lockVersion = null)
 * @method DismissedJobTitle|null findOneBy(array $criteria, array $orderBy = null)
 * @method DismissedJobTitle[]    findAll()
 * @method DismissedJobTitle[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DismissedJobTitleRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, DismissedJobTitle::class);
    }

    // /**
    //  * @return DismissedJobTitle[] Returns an array of DismissedJobTitle objects
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
    public function findOneBySomeField($value): ?DismissedJobTitle
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
