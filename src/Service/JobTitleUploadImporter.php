<?php

namespace App\Service;

use App\Entity\City;
use App\Entity\City\County;
use App\Entity\City\Department;
use App\Entity\City\JobTitle;
use App\Entity\JobTitle\Lookup\JobCategory;
use App\Entity\JobTitle\Lookup\JobLevel;
use App\Entity\JobTitle\Lookup\JobTitleName;
use App\Entity\JobTitle\Lookup\JobType;
use App\Entity\City\State;
use App\Repository\City\DivisionRepository;
use App\Repository\CityRepository;
use App\Repository\City\CountyRepository;
use App\Repository\City\DepartmentRepository;
use App\Repository\City\JobTitleRepository;
use App\Repository\JobTitle\Lookup\JobCategoryRepository;
use App\Repository\JobTitle\Lookup\JobLevelRepository;
use App\Repository\JobTitle\Lookup\JobTitleNameRepository;
use App\Repository\JobTitle\Lookup\JobTypeRepository;
use App\Repository\City\StateRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Sonata\AdminBundle\Exception\ModelManagerException;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Validator\Constraints\Date;

class JobTitleUploadImporter
{


    /** @var SessionInterface $session */
    private $session;

    /** @var EntityManager $em */
    private $em;

    public function __construct(
        SessionInterface $session,
        EntityManagerInterface $em
    )
    {
        $this->session = $session;
        $this->em = $em;
    }

    public function import($file)
    {
        $header = array_shift($file);
        $header = array_flip($header);

        foreach ($file as $index=>$row) {
            if ($row[$header['cityName']] == '' && $row[$header['stateName']] == '' && $row[$header['countyName']] == '') {
                unset($file[$index]);
            }
        }

        foreach ($file as $index=>$defaultValue) {

            if($defaultValue[$header['cityName']] == '' || $defaultValue[$header['countyName']] == '' || $defaultValue[$header['stateName']] == ''){
                $this->session->getBag('flashes')->add('error', 'Every record must contain a City, State & County');
                return false;
            }

            /* @var $stateRepository StateRepository */
            $stateRepository = $this->em->getRepository(State::class);
            $state = $stateRepository->findOneBy([
                'name' => $defaultValue[$header['stateName']]
            ]);

            //CIT-422: In JobTitle Importer, add column "isClosedPromotional" to fixture and confirm importer handles it correctly.
            if ( ! isset($header['isClosedPromotional']) || $header['isClosedPromotional'] == null) {
                $this->session->getBag('flashes')->add('error', 'IsClosedPromotional not found.');
                return false;
            }

            if ($defaultValue[$header['isClosedPromotional']] != '0' && $defaultValue[$header['isClosedPromotional']] != 1 && $defaultValue[$header['isClosedPromotional']] != '') {
                $this->session->getBag('flashes')->add('error', 'IsClosedPromotional must be 0 or 1.');
                return false;
            }
            //end

            // GLR 2019-04-06 Job Title importer should error on non-existent State
            if (false == $state) {
                $this->session->getBag('flashes')->add('error', 'State with name: "'.$defaultValue[$header['stateName']].'" not found."');
                return false;
            }

//            if($defaultValue[$header['stateName']] != '' && $state == null) {
//                $state = new State();
//
//                $state->setName($defaultValue[$header['stateName']]);
//                $state->setIsActive(false);
//
//                $this->em->persist($state);
//                $this->em->flush($state);
//            }

            /* @var $countyRepository CountyRepository */
            $countyRepository = $this->em->getRepository(County::class);
            $county = $countyRepository->findOneBy([
                'name' => $defaultValue[$header['countyName']],
                'state' => $state,
            ]);

            // GLR 2019-04-06 Job Title importer should error on non-existent County
            if (false == $county) {
                $this->session->getBag('flashes')->add('error', 'County with name: "'.$defaultValue[$header['countyName']].'" not found."');
                return false;
            }

//            if($defaultValue[$header['countyName']] != '' && $county == null) {
//                $countyName = $defaultValue[$header['countyName']];
//
//                $county = new County();
//
//                $county->setName($countyName);
//                $county->setState($state);
//
//                $this->em->persist($county);
//                $this->em->flush($county);
//            }

            /* @var $cityRepository CityRepository */
            $cityRepository = $this->em->getRepository(City::class);

            $cityName = $defaultValue[$header['cityName']];
            $city = $cityRepository->findOneByCounty($cityName, $county);

            // GLR 2019-04-06 Job Title importer should error on non-existent City
            if (false == $city) {
                $this->session->getBag('flashes')->add('error', 'City with name: "'.$defaultValue[$header['cityName']].'" not found."');
                return false;
            }

//            if($defaultValue[$header['cityName']] != '' && $city == null) {
//                $city = new City();
//
//                $city->setName($defaultValue[$header['cityName']]);
//
//                if($county){
//                    $city->addCounty($county);
//                    $county->addCity($city);
//
//                    $this->em->persist($county);
//                }
//                $this->em->persist($city);
//            }

            $city->setJobTitlesAddedDate(new \DateTime('now', new \DateTimeZone('UTC')));
            $this->em->persist($city);

            /** @var JobTitleNameRepository $jobTitleNameRepository */
            $jobTitleNameRepository = $this->em->getRepository(JobTitleName::class);
            $jobTitleName = null;
            $jobTitleName = $jobTitleNameRepository->findOneBy(['name' => $defaultValue[$header['jobTitle']]]);
            if ($defaultValue[$header['jobTitle']] != '' && $jobTitleName == null) {
                $jobTitleName = new JobTitleName();
                $jobTitleName->setName($defaultValue[$header['jobTitle']]);
                $this->em->persist($jobTitleName);
                $this->em->flush();
            }

            /* @var $departmentRepository DepartmentRepository */
            $departmentRepository = $this->em->getRepository(Department::class);

            $department = null;
            $department = $departmentRepository->findOneBy([
                'name' => $defaultValue[$header['department']],
                'city' => $city,
            ]);

            if ($defaultValue[$header['department']] != '' && $department == null) {
                $department = new Department();
                $department->setName($defaultValue[$header['department']]);
                $department->setCity($city);

                $orderBy = 0;
                foreach ($city->getDepartments() as $dpm) {
                    $orderBy = max($orderBy, $dpm->getOrderByNumber());
                }
                $department->setOrderByNumber($orderBy + 1);

                $this->em->persist($department);

                $this->em->flush($department);

                $city->addDepartment($department);
                $this->em->persist($city);
            }

            // CIT-575: job titles may only have different job types
            $jobType = null;
            if ($defaultValue[$header['jobType']] != '') {
                /* @var $jobTypeRepository JobTypeRepository */
                $jobTypeRepository = $this->em->getRepository(JobType::class);

                $jobTypeName = $defaultValue[$header['jobType']];
                $jobType     = $jobTypeRepository->findOneByName($jobTypeName);

                if ($jobType == null) {
                    $jobType = new JobType();
                    $jobType->setName($defaultValue[$header['jobType']]);

                    $this->em->persist($jobType);
                    $this->em->flush($jobType);
                }
            }

            $jobLevel = null;
            if ($defaultValue[$header['level']] != '') {
                /* @var $jobLevelRepository JobLevelRepository */
                $jobLevelRepository = $this->em->getRepository(JobLevel::class);

                $jobLevelSlug = strtolower($defaultValue[$header['level']]);
                $jobLevel = $jobLevelRepository->findOneBySlug($jobLevelSlug);

                if($jobLevel == null) {
                    $jobLevel = new JobLevel();
                    $jobLevel->setName($defaultValue[$header['level']]);

                    $this->em->persist($jobLevel);
                    $this->em->flush($jobLevel);
                }
            }

            /* @var $divisionRepository DivisionRepository */
            $divisionRepository = $this->em->getRepository(City\Division::class);
            $division           = null;
            $division           = $divisionRepository->findOneBy([
                'name'       => $defaultValue[$header['division']],
                'city'       => $city,
                'department' => $department,
            ]);

            if ($defaultValue[$header['division']] != '' && $division == null) {
                $division = new City\Division();
                $division->setName($defaultValue[$header['division']]);
                $division->setCity($city);
                $division->setDepartment($department);

                $this->em->persist($division);
                $this->em->flush($division);

                $department->addDivision($division);
                $city->addDivision($division);
                $this->em->persist($department);
                $this->em->persist($city);
            }

            /* @var $jobTitleRepository JobTitleRepository */
            $jobTitleRepository = $this->em->getRepository(JobTitle::class);
            $jobTitle           = null;
            $searchFilter       = [
                'jobTitleName' => $jobTitleName,
                'city'         => $city
            ];

            if ($department) {
                $searchFilter['department'] = $department;
            }
            if ($division) {
                $searchFilter['division'] = $division;
            }
            if ($jobType) {
                $searchFilter['type'] = $jobType;
            }
            if ($jobLevel) {
                $searchFilter['level'] = $jobLevel;
            }

            $jobTitle = $jobTitleRepository->findOneBy($searchFilter);

            if ($jobTitleName) {
                if($jobTitle == null) {
                    $jobTitle = new JobTitle();
                    $jobTitle->setJobTitleName($jobTitleName);
                    $jobTitle->setCity($city);
                }
            }

            if($department != null && $jobTitle != null) {
                $jobTitle->setDepartment($department);
            }

            if($department != null && $jobTitle != null && $division != null) {
                $jobTitle->setDivision($division);
            }

            if ($jobType && $jobTitle) {
                $jobTitle->setType($jobType);
            }

            if ($jobLevel && $jobTitle) {
                $jobTitle->setLevel($jobLevel);
            }

            if($defaultValue[$header['jobCategoryOne']] != '') {
                $jobCategoryName = $defaultValue[$header['jobCategoryOne']];

                $jobCategoryOne = $this->findOrCreateJobCategory($jobCategoryName);
                $jobTitle->addCategory($jobCategoryOne);
            }

            if($defaultValue[$header['jobCategoryTwo']] != '') {
                $jobCategoryName = $defaultValue[$header['jobCategoryTwo']];

                $jobCategoryTwo = $this->findOrCreateJobCategory($jobCategoryName);
                $jobTitle->addCategory($jobCategoryTwo);
            }

            if($defaultValue[$header['jobCategoryThree']] != '') {
                $jobCategoryName = $defaultValue[$header['jobCategoryThree']];

                $jobCategoryThree = $this->findOrCreateJobCategory($jobCategoryName);
                $jobTitle->addCategory($jobCategoryThree);
            }

            if($defaultValue[$header['jobCategoryFour']] != '') {
                $jobCategoryName = $defaultValue[$header['jobCategoryFour']];

                $jobCategoryFour = $this->findOrCreateJobCategory($jobCategoryName);
                $jobTitle->addCategory($jobCategoryFour);
            }

            if($defaultValue[$header['jobCategoryFive']] != '') {
                $jobCategoryName = $defaultValue[$header['jobCategoryFive']];

                $jobCategoryFive = $this->findOrCreateJobCategory($jobCategoryName);
                $jobTitle->addCategory($jobCategoryFive);
            }

            if ($defaultValue[$header['positionCount']] != '') {
                $positionCount = (int) $defaultValue[$header['positionCount']];
                $jobTitle->setPositionCount($positionCount);
            }

            if ($defaultValue[$header['monthlySal-low']] != '') {
                $monthlySalaryLow = $defaultValue[$header['monthlySal-low']];
                $jobTitle->setMonthlySalaryLow($monthlySalaryLow);
            }

            if ($defaultValue[$header['monthlySal-high']] != '') {
                $monthlySalaryHigh = $defaultValue[$header['monthlySal-high']];
                $jobTitle->setMonthlySalaryHigh($monthlySalaryHigh);
            }

            if ($defaultValue[$header['hrlyWage-low']] != '') {
                $hourlyWageLow = $defaultValue[$header['hrlyWage-low']];
                $jobTitle->setHourlyWageLow($hourlyWageLow);
            }

            if ($defaultValue[$header['hrlyWage-high']] != '') {
                $hourlyWageHigh = $defaultValue[$header['hrlyWage-high']];
                $jobTitle->setHourlyWageHigh($hourlyWageHigh);
            }

            if ($defaultValue[$header['isClosedPromotional']] != '') {
                $isClosedPromotional = $defaultValue[$header['isClosedPromotional']];
                $jobTitle->setIsClosedPromotional($isClosedPromotional);
            }

            $this->em->persist($jobTitle);
            $this->em->flush();
        }
        return true;

    }

    public function findOrCreateJobCategory($jobCategoryName){
        /* @var $jobCategoryRepository JobCategoryRepository */
        $jobCategoryRepository = $this->em->getRepository(JobCategory::class);

        $jobCategory = $jobCategoryRepository->findOneBy([
            'name' => $jobCategoryName,
        ]);

        if ($jobCategory == null) {
            $jobCategory = new JobCategory();
            $jobCategory->setName($jobCategoryName);
            $jobCategory->setIsGeneral(false);

            $this->em->persist($jobCategory);
            $this->em->flush($jobCategory);
        }

        return $jobCategory;
    }
}