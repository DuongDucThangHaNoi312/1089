<?php

namespace App\Repository\User\JobSeekerUser;

use App\Entity\City;
use App\Entity\User\CityUser\SavedResume;
use App\Entity\User\JobSeekerUser\Resume;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\Common\Collections\Collection;

/**
 * @method Resume|null find($id, $lockMode = null, $lockVersion = null)
 * @method Resume|null findOneBy(array $criteria, array $orderBy = null)
 * @method Resume[]    findAll()
 * @method Resume[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ResumeRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Resume::class);
    }

//    /**
//     * @return Resume[] Returns an array of Resume objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Resume
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    /**
     * @param City|null $forCity
     * @param []|null $searchData
     * @return Query
     */
    public function getQueryWithSearchFilterData(?City $forCity, $searchData = null) {

        $qb = $this->createQueryBuilder('resume')
            ->andWhere('resume.isComplete = true')
            ->andWhere('resume.isAvailableForSearch = true')
            ->leftJoin('resume.education', 'education')
            ->leftJoin('resume.interestedJobCategories', 'category')
            ->leftJoin('resume.interestedJobLevels', 'level')
            ->leftJoin('resume.interestedJobTitleNames', 'job_title')
            ->orderBy('resume.firstname', 'ASC')
        ;

        /** This only applies when Searching for Resumes */
        if ($forCity) {
            $qb2 = $this->createQueryBuilder('resume')
                ->select('resume.id')
                ->leftJoin('resume.citiesToBlock', 'cities_to_block')
                ->andWhere('cities_to_block IN (:cities)')
                ->setParameter('cities',[$forCity->getId()]);
                $result = $qb2->getQuery()->getArrayResult();
                $mappedResult = array_map(function($item) {
                    return reset($item);
                }, $result);
                $mappedResult = array_unique($mappedResult);

            if (count($mappedResult) != 0) {
                $qb
                    ->andWhere('resume.id NOT IN (:resumes)')
                    ->setParameter('resumes',$mappedResult);
            }
        }


        if (isset($searchData['state']) || isset($searchData['counties'])) {
            $qb->leftJoin('resume.interestedCounties', 'counties');
            if (isset($searchData['state']) && $searchData['state']) {
                $qb->andWhere('counties.state = :stateId')
                    ->setParameter('stateId', $searchData['state']);
            }
            if (isset($searchData['counties']) && count($searchData['counties'])) {
                $qb->andWhere('counties IN (:countyIds)')
                    ->setParameter('countyIds', $searchData['counties']);
            }
        }

        if (isset($searchData['jobCategories']) && count($searchData['jobCategories'])) {
            $qb
                ->andWhere('category IN (:jobCategoryIds)')
                ->setParameter('jobCategoryIds', $searchData['jobCategories']);
        }

        if (isset($searchData['jobTitles']) && count($searchData['jobTitles'])) {
            $qb->andWhere('job_title IN (:jobTitleIds)')
                ->setParameter('jobTitleIds', $searchData['jobTitles']);
        }

        if (isset($searchData['jobLevels']) && count($searchData['jobLevels'])) {
            $qb->andWhere('level IN (:jobLevelIds)')
                ->setParameter('jobLevelIds', $searchData['jobLevels']);
        }


        if (isset($searchData['jobTypes']) && count($searchData['jobTypes'])) {
            $qb->andWhere('resume.interestedJobType IN (:jobTypeIds)')
                ->setParameter('jobTypeIds', $searchData['jobTypes']);
        }

        if(isset($searchData['yearsOfExperience']) && $searchData['yearsOfExperience'] != 0) {
            $qb->andWhere('resume.yearsWorkedInCityGovernment >= :years')
                ->setParameter('years', $searchData['yearsOfExperience']);
        }

        if (isset($searchData['educationLevel']) && count($searchData['educationLevel'])) {
            $qb->andWhere('education.degreeType IN (:educationLevel)')
                ->setParameter('educationLevel', $searchData['educationLevel']);
        }


        return $qb->getQuery();
    }
}
