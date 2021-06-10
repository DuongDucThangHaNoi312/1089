<?php

namespace App\Repository\User;

use App\Entity\User;
use App\Entity\User\JobSeekerUser;
use App\Entity\User\SavedCity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method SavedCity|null find($id, $lockMode = null, $lockVersion = null)
 * @method SavedCity|null findOneBy(array $criteria, array $orderBy = null)
 * @method SavedCity[]    findAll()
 * @method SavedCity[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SavedCityRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, SavedCity::class);
    }

    public function getUserSavedCityIDs(User $user)
    {
        return $this->createQueryBuilder('saved_city')
            ->select('IDENTITY(saved_city.city)')
            ->where('saved_city.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult('ColumnHydrator');
    }

    public function findByUser(User $user, $maxResults = 10) {

        $queryBuilder = $this->createQueryBuilder('saved_city')
            ->leftJoin('saved_city.city', 'city')
            ->leftJoin('city.counties', 'county')
            ->andWhere('saved_city.user = :user')
            ->andWhere('city.prefix IS NOT NULL')
            ->andWhere('county.isActive = 1')
            ->setParameter('user', $user);

        if ($user instanceof User\JobSeekerUser) {
            if ($user->getSubscription() && $user->getSubscription()->getSubscriptionPlan()->getLimitCityLinkSearchToCountyOfResidence()) {
                $queryBuilder->andWhere('county.id = :county')
                    ->setParameter('county', $user->getCounty());
            }
        }

        return $queryBuilder
            ->orderBy('county.name, city.name', 'ASC')
            ->getQuery()
            ->setMaxResults($maxResults)
            ->getResult();
    }

    public function getTotalSavedLinksByUser(User $user) {
        $queryBuilder = $this->createQueryBuilder('saved_city')
            ->select('COUNT(saved_city.id) as total')
            ->leftJoin('saved_city.city', 'city')
            ->leftJoin('city.counties', 'county')
            ->andWhere('saved_city.user = :user')
            ->andWhere('county.isActive = 1')
            ->setParameter('user', $user);
        return $queryBuilder
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function deleteByJobSeeker(JobSeekerUser $jobSeeker)
    {
        $qb = $this->createQueryBuilder('sc')
                   ->delete()
                   ->where('sc.user = :userId')
                   ->setParameter('userId', $jobSeeker->getId());

        $qb->getQuery()->execute();
    }

    public function findByUserId($value)
    {
        return $this->createQueryBuilder('s')
                    ->andWhere('s.user = :userId')
                    ->setParameter('userId', $value)
                    ->getQuery()
                    ->getResult();
    }
}
