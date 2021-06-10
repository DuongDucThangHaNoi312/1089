<?php

namespace App\Repository\User\CityUser;

use App\Entity\User\CityUser;
use App\Entity\User\CityUser\SavedResume;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method SavedResume|null find($id, $lockMode = null, $lockVersion = null)
 * @method SavedResume|null findOneBy(array $criteria, array $orderBy = null)
 * @method SavedResume[]    findAll()
 * @method SavedResume[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SavedResumeRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, SavedResume::class);
    }

//    /**
//     * @return SavedResume[] Returns an array of SavedResume objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?SavedResume
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function getSavedResumesForDashboardForUser(CityUser $user, $maxResults = 4) {
        $queryBuilder = $this->createQueryBuilder('saved_resume')
            ->leftJoin('saved_resume.resume', 'resume')
            ->leftJoin('saved_resume.cityUser', 'city_user')
            ->andWhere('city_user.id IN (:users)')
            ->setParameter('users',[$user->getId()])
            ->orderBy('resume.firstname', 'ASC');


        return $queryBuilder
            ->getQuery()
            ->setMaxResults($maxResults)
            ->getResult();
    }

    public function getTotalSavedResumesForUser(CityUser $user) {
        $queryBuilder = $this->createQueryBuilder('saved_resume')
            ->select('COUNT(saved_resume.id) as total')
            ->leftJoin('saved_resume.resume', 'resume')
            ->leftJoin('saved_resume.cityUser', 'city_user')
            ->andWhere('city_user.id IN (:users)')
            ->setParameter('users',[$user->getId()])
            ->groupBy('city_user.id')
            ;

        try {
            $result = $queryBuilder->getQuery()->getSingleScalarResult();
            return $result;
        } catch (\Doctrine\ORM\NoResultException $e) {
            // Handle the exception here. In this case, we are setting the variable to NULL
            $result = 0;
            return $result;
        }
    }

    /**
     * @param CityUser|null $forCity
     * @param null $searchData
     * @param SavedResume[] $savedResumes
     * @return \Doctrine\ORM\Query
     */
    public function getQueryWithSearchFilterData(?CityUser $forUser, $searchData = null) {

        $qb = $this->createQueryBuilder('savedResume')
            ->leftJoin('savedResume.resume', 'resume')
            ->leftJoin('savedResume.cityUser', 'user')
            ->leftJoin('resume.education', 'education')
            ->leftJoin('resume.interestedJobCategories', 'category')
            ->leftJoin('resume.interestedJobLevels', 'level')
            ->leftJoin('resume.interestedJobTitleNames', 'job_title')
            ->orderBy('resume.firstname', 'ASC')
        ;

        if ($forUser) {
            $qb
                ->andWhere('user.id IN (:users)')
                ->setParameter('users',[$forUser->getId()]);
        }

        if (isset($searchData['state']) || isset($searchData['counties'])) {
            $qb->leftJoin('resume.interestedCounties', 'counties');
            if (isset($searchData['state']) && $searchData['state']) {
                $qb->andWhere('counties.state = :stateId')
                    ->setParameter('stateId', $searchData['state']);
            }
            if (isset($searchData['counties']) && $searchData['counties']) {
                $qb->andWhere('counties IN (:countyIds)')
                    ->setParameter('countyIds', $searchData['counties']);
            }
        }

        if (isset($searchData['jobCategories']) && $searchData['jobCategories']) {
            $qb
                ->andWhere('category IN (:jobCategoryIds)')
                ->setParameter('jobCategoryIds', $searchData['jobCategories']);
        }

        if (isset($searchData['jobTitles']) && $searchData['jobTitles']) {
            $qb->andWhere('job_title IN (:jobTitleIds)')
                ->setParameter('jobTitleIds', $searchData['jobTitles']);
        }

        if (isset($searchData['jobLevels']) && $searchData['jobLevels']) {
            $qb->andWhere('resume.interestedJobLevel IN (:jobLevelIds)')
                ->setParameter('jobLevelIds', $searchData['jobLevels']);
        }


        if (isset($searchData['jobTypes']) && $searchData['jobTypes']) {
            $qb->andWhere('resume.interestedJobType IN (:jobTypeIds)')
                ->setParameter('jobTypeIds', $searchData['jobTypes']);
        }

        if(isset($searchData['yearsOfExperience']) && $searchData['yearsOfExperience'] != 0) {
            $qb->andWhere('resume.yearsWorkedInCityGovernment >= :years')
                ->setParameter('years', $searchData['yearsOfExperience']);
        }

        if (isset($searchData['educationLevel']) && $searchData['educationLevel']) {
            $qb->andWhere('education.degreeType IN (:educationLevel)')
                ->setParameter('educationLevel', $searchData['educationLevel']);
        }

        return $qb->getQuery();
    }
}
