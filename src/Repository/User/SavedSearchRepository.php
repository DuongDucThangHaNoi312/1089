<?php

namespace App\Repository\User;

use App\Entity\User\SavedSearch;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method SavedSearch|null find($id, $lockMode = null, $lockVersion = null)
 * @method SavedSearch|null findOneBy(array $criteria, array $orderBy = null)
 * @method SavedSearch[]    findAll()
 * @method SavedSearch[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SavedSearchRepository extends ServiceEntityRepository
{
    /**
     * SavedSearchRepository constructor.
     *
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, SavedSearch::class);
    }

    /**
     * @param $userId
     * @param $savedSearchToken
     *
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findByUserAndToken($userId, $savedSearchToken)
    {
        return $this->createQueryBuilder('s')
                    ->andWhere('s.user = :userId')
                    ->setParameter('userId', $userId)
                    ->andWhere('s.searchQuery LIKE :token')
                    ->setParameter('token', "%saved_search=$savedSearchToken%")
                    ->getQuery()
                    ->getOneOrNullResult();
    }

    public function findByUserId($value)
    {
        return $this->createQueryBuilder('s')
                    ->andWhere('s.user = :userId')
                    ->setParameter('userId', $value)
                    ->getQuery()
                    ->getResult();
    }

    public function findLikeUri($uri, $userId, $isDefault = false) {
        $qb = $this->createQueryBuilder('s')
                    ->andWhere('s.user = :userId')
                    ->setParameter('userId', $userId)
                    ->andWhere('s.searchQuery LIKE :uri')
                    ->setParameter('uri', '%' . $uri . '%')
                    ->setMaxResults(1);

        if ($isDefault == true) {
            $qb
                ->andWhere('s.isDefault = 1');
        }

        return $qb->getQuery()->getOneOrNullResult();
    }
}
