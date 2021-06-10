<?php

namespace App\Service;


use Doctrine\ORM\QueryBuilder;

class ProfileDataSearchHelper
{
    public function filterQueryBuilderByProfileData(QueryBuilder $qb, $jobTitleAlias, $jobAnnouncementAlias, $cityAlias, $jobTitleNameAlias, $searchData = [])
    {
        if (isset($searchData['saved']) && $searchData['saved']) {
            if (isset($searchData['isJobAnnouncement']) && $searchData['isJobAnnouncement']) {
                $qb->leftJoin("$jobAnnouncementAlias.savedJobAnnouncements", 'savedJobAnnouncements')
                   ->leftJoin('savedJobAnnouncements.jobSeekerUser', 'jobSeekerUser1')
                   ->andWhere('jobSeekerUser1 = :user')
                   ->setParameter('user', $searchData['user']);
            }
            else {
                $qb->leftJoin("$jobTitleAlias.savedJobTitles", 'savedJobTitles')
                   ->leftJoin('savedJobTitles.jobSeekerUser', 'jobSeekerUser1')
                   ->andWhere('jobSeekerUser1 = :user')
                   ->setParameter('user', $searchData['user']);
            }

        }

        if (isset($searchData['searchSubmittedJobTitle']) && $searchData['searchSubmittedJobTitle']) {
            $qb->join("$jobTitleAlias.submittedJobTitleInterests", 'submittedJobTitleInterests')
               ->join('submittedJobTitleInterests.jobSeekerUser', 'jobSeekerUser2')
               ->andWhere('jobSeekerUser2 = :user')
               ->setParameter('user', $searchData['user']);
        }


        // CIT-424: show closed promotional jobs ONLY IF Job Seeker is currently working for the city.
        if (isset($searchData['worksForCity'])) {
            $qb->andWhere("($jobTitleAlias.isClosedPromotional = 0) OR ($jobTitleAlias.isClosedPromotional = 1 AND $cityAlias.id = :cityId)")
               ->setParameter('cityId', $searchData['worksForCity']);
        }
        else {
            $qb->andWhere("$jobTitleAlias.isClosedPromotional = 0");
        }

        if (isset($searchData['state']) || isset($searchData['counties'])) {
            if (isset($searchData['state']) && $searchData['state']) {
                $qb->andWhere('counties.state = :stateId')
                   ->setParameter('stateId', $searchData['state']);
            }

            if (isset($searchData['counties']) && count($searchData['counties'])) {
                $qb->andWhere('counties IN (:countyIds)')
                   ->setParameter('countyIds', $searchData['counties']);
            }
        }

        if (isset($searchData['cities']) && count($searchData['cities'])) {
            $qb->andWhere("$cityAlias IN (:cityIds)")
               ->setParameter('cityIds', $searchData['cities']);
        }

        if (isset($searchData['jobCategories']) && count($searchData['jobCategories'])) {
            foreach ($searchData['jobCategories'] as $category) {
                $catId = null;
                if (is_string($category)) {
                    $catId = $category;
                }
                else {
                    $catId = $category->getId();
                }

                $qb->join("$jobTitleAlias.category", "job_category_$catId")
                   ->andWhere("job_category_$catId.id = :jcId$catId")
                   ->setParameter("jcId$catId", $catId);
            }
        }

        if (isset($searchData['jobTitleNames']) && count($searchData['jobTitleNames'])) {
            $qb->andWhere("$jobTitleNameAlias IN (:jobTitleNameIds)")
               ->setParameter('jobTitleNameIds', $searchData['jobTitleNames']);
        }
        if (isset($searchData['jobLevels']) && count($searchData['jobLevels'])) {
            $qb->andWhere("$jobTitleAlias.level IN (:jobLevelIds)")
               ->setParameter('jobLevelIds', $searchData['jobLevels']);
        }
        if (isset($searchData['jobTypes']) && count($searchData['jobTypes'])) {
            $qb->andWhere("$jobTitleAlias.type IN (:jobTypeIds)")
               ->setParameter('jobTypeIds', $searchData['jobTypes']);
        }


        if (isset($searchData['population']) && $searchData['population']) {
            $qb->leftJoin("$cityAlias.censusPopulations", 'census_populations');
            $population = array_map('trim', explode(';', $searchData['population']));

            if (count($population) > 1) {
                $populationMin = $population[0];
                $populationMax = end($population);
            } elseif (count($population) == 1) {
                $populationMin = $population[0];
            }

            if (isset($populationMin) && $populationMin) {
                $qb->andWhere('census_populations.population >= :populationMin')
                   ->andWhere('census_populations.year = (SELECT MAX(cp3.year) FROM App:City\CensusPopulation cp3)')
                   ->setParameter('populationMin', $populationMin);
            }
            if (isset($populationMax) && $populationMax) {
                $qb->andWhere('census_populations.population <= :populationMax')
                   ->setParameter('populationMax', $populationMax);
            }
        }

        if (isset($searchData['employees']) && $searchData['employees']) {
            $employees = array_map('trim', explode(';', $searchData['employees']));

            if (count($employees) > 1) {
                $employeesMin = $employees[0];
                $employeesMax = end($employees);
            } elseif (count($employees) == 1) {
                $employeesMin = $employees[0];
            }

            if (isset($employeesMin) && $employeesMin) {
                $qb->andWhere("$cityAlias.countFTE >= :employeeMin")
                   ->setParameter('employeeMin', $employeesMin);
            }
            if (isset($employeesMax) && $employeesMax) {
                $qb->andWhere("$cityAlias.countFTE <= :employeeMax")
                   ->setParameter('employeeMax', $employeesMax);
            }
        }
    }
}
