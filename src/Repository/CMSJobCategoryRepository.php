<?php

namespace App\Repository;

use App\Entity\CMSJobCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method CMSJobCategory|null find($id, $lockMode = null, $lockVersion = null)
 * @method CMSJobCategory|null findOneBy(array $criteria, array $orderBy = null)
 * @method CMSJobCategory[]    findAll()
 * @method CMSJobCategory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CMSJobCategoryRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CMSJobCategory::class);
    }


    public function findAllWithNameOrdered()
    {
        return $this->createQueryBuilder('c')
                    ->orderBy('c.name', 'ASC')
                    ->getQuery()
                    ->getResult();
    }
}
