<?php

namespace App\Service;

use App\Entity\City\CensusPopulation;
use App\Entity\City;
use App\Entity\City\County;
use App\Entity\Lookup\UrlType;
use App\Entity\City\OperationHours;
use App\Entity\City\State;
use App\Entity\Url;
use App\Repository\City\CensusPopulationRepository;
use App\Repository\CityRepository;
use App\Repository\City\CountyRepository;
use App\Repository\Lookup\UrlTypeRepository;
use App\Repository\City\OperationHoursRepository;
use App\Repository\City\StateRepository;
use App\Repository\UrlRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Sonata\AdminBundle\Exception\ModelManagerException;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CityProfileUploadImporter
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
            if($row[$header['cityName']] == ''){
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

            // GLR 2019-04-06 City Profile importer should error on non-existent State
            if (false == $state) {
                $this->session->getBag('flashes')->add('error', 'State with name: "'.$defaultValue[$header['stateName']].'" not found."');
                return false;
            }

//            if($defaultValue[$header['stateName']] != '' && $state == null) {
//                $state = new State();
//
//                $state->setName($defaultValue[$header['stateName']]);
//                $state->setIsActive(true);
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

            // GLR 2019-04-06 City Profile importer should error on non-existent County
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

            // GLR 2019-04-06 City Profile importer should error on non-existent City
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

            if ($defaultValue[$header['address']] != '') {
                $city->setAddress($defaultValue[$header['address']]);
            }

            if ($defaultValue[$header['zipCode']] != '') {
                $city->setZipCode($defaultValue[$header['zipCode']]);
            }

            if ($defaultValue[$header['passcode']] != '') {
                $city->setPasscode($defaultValue[$header['passcode']]);
            }

            if ($defaultValue[$header['cityHallPhone']] != '') {
                $city->setCityHallPhone($defaultValue[$header['cityHallPhone']]);
            }

            if ($defaultValue[$header['jobsHotline']] != '') {
                $city->setJobsHotline($defaultValue[$header['jobsHotline']]);
            }

            if ($defaultValue[$header['yearFounded']] != '') {
                $city->setYearFounded($defaultValue[$header['yearFounded']]);
            }

            if ($defaultValue[$header['yearChartered']] != '') {
                $city->setYearChartered($defaultValue[$header['yearChartered']]);
            }

            if ($defaultValue[$header['yearIncorporated']] != '') {
                $city->setYearIncorporated($defaultValue[$header['yearIncorporated']]);
            }

            if ($defaultValue[$header['squareMiles']] != '') {
                $city->setSquareMiles($defaultValue[$header['squareMiles']]);
            }

            if ($defaultValue[$header['countFTE']] != '') {
                $city->setCountFTE($defaultValue[$header['countFTE']]);
            }

            /* HR Director information */
            if ($defaultValue[$header['hrDirectorFirstName']] != '') {
                $city->setHrDirectorFirstName($defaultValue[$header['hrDirectorFirstName']]);
            }

            if ($defaultValue[$header['hrDirectorLastName']] != '') {
                $city->setHrDirectorLastName($defaultValue[$header['hrDirectorLastName']]);
            }

            if ($defaultValue[$header['hrNamePrefix']] != '') {
                $city->setHrNamePrefix($defaultValue[$header['hrNamePrefix']]);
            }

            if ($defaultValue[$header['hrNameSuffix']] != '') {
                $city->setHrNameSuffix($defaultValue[$header['hrNameSuffix']]);
            }

            if ($defaultValue[$header['hrDirectorTitle']] != '') {
                $city->setHrDirectorTitle($defaultValue[$header['hrDirectorTitle']]);
            }

            if ($defaultValue[$header['hrDirectorPhone']] != '') {
                $city->setHrDirectorPhone($defaultValue[$header['hrDirectorPhone']]);
            }

            if ($defaultValue[$header['hrDirectorEmail']] != '') {
                $city->setHrDirectorEmail($defaultValue[$header['hrDirectorEmail']]);
            }
            /* End of HR Director information */

            /* Census Population */
            /* @var $censusPopulationRepository CensusPopulationRepository */
            $censusPopulationRepository = $this->em->getRepository(CensusPopulation::class);

            if ($defaultValue[$header['censusPopulation2000']] != '') {

                $censusPopulation2000 = $censusPopulationRepository->findOneBy([
                    'city' => $city,
                    'year' => '2000'
                ]);

                if($censusPopulation2000 == null) {
                    $censusPopulation2000 = new CensusPopulation();
                    $censusPopulation2000->setYear(2000);
                    $censusPopulation2000->setCity($city);
                }

                $censusPopulation = preg_replace('/\D+/', '', $defaultValue[$header['censusPopulation2000']]);
                $censusPopulation2000->setPopulation($censusPopulation);
                $this->em->persist($censusPopulation2000);

                $city->addCensusPopulation($censusPopulation2000);
            }

            if ($defaultValue[$header['censusPopulation2010']] != '') {

                $censusPopulation2010 = $censusPopulationRepository->findOneBy([
                    'city' => $city,
                    'year' => '2010'
                ]);

                if($censusPopulation2010 == null) {
                    $censusPopulation2010 = new CensusPopulation();
                    $censusPopulation2010->setYear(2010);
                    $censusPopulation2010->setCity($city);
                }

                $censusPopulation = preg_replace('/\D+/', '', $defaultValue[$header['censusPopulation2010']]);
                $censusPopulation2010->setPopulation($censusPopulation);
                $this->em->persist($censusPopulation2010);

                $city->addCensusPopulation($censusPopulation2010);
            }
            /* End Census Population */


            /* City URLs */
            if ($defaultValue[$header['homePageURL']] != '') {
                $urlType = 'Home Page';
                $urlValue = $defaultValue[$header['homePageURL']];

                $this->findOrCreateURL($urlType, $urlValue, $city);
            }

            if ($defaultValue[$header['humanResourcesURL']] != '') {
                $urlType = 'Human Resources';
                $urlValue = $defaultValue[$header['humanResourcesURL']];

                $this->findOrCreateURL($urlType, $urlValue, $city);
            }

            if ($defaultValue[$header['jobListingURL']] != '') {
                $urlType = 'Job Announcements';
                $urlValue = $defaultValue[$header['jobListingURL']];

                $this->findOrCreateURL($urlType, $urlValue, $city);
            }

            if ($defaultValue[$header['jobDescriptionURL']] != '') {
                $urlType = 'Job Descriptions';
                $urlValue = $defaultValue[$header['jobDescriptionURL']];

                $this->findOrCreateURL($urlType, $urlValue, $city);
            }

            if ($defaultValue[$header['orgChartURL']] != '') {
                $urlType = 'Organization Chart';
                $urlValue = $defaultValue[$header['orgChartURL']];

                $this->findOrCreateURL($urlType, $urlValue, $city);
            }

            if ($defaultValue[$header['laborAgreementsURL']] != '') {
                $urlType = 'Labor Agreements';
                $urlValue = $defaultValue[$header['laborAgreementsURL']];

                $this->findOrCreateURL($urlType, $urlValue, $city);
            }

            if ($defaultValue[$header['salaryTableURL']] != '') {
                $urlType = 'Salary Tables';
                $urlValue = $defaultValue[$header['salaryTableURL']];

                $this->findOrCreateURL($urlType, $urlValue, $city);
            }
            /* End City URLs */


            /* Operation Hours */
            if ($defaultValue[$header['mondayOpen']] != '' && $defaultValue[$header['mondayOpen']] != 'Closed'
                && $defaultValue[$header['mondayClose']] != '' && $defaultValue[$header['mondayClose']] != 'Closed') {
                $dayOfWeek = 'Monday';
                $open = $defaultValue[$header['mondayOpen']];
                $close = $defaultValue[$header['mondayClose']];

                $this->findOrCreateOperationHour($dayOfWeek, $open, $close, $city);
            }

            if ($defaultValue[$header['tuesdayOpen']] != '' && $defaultValue[$header['tuesdayOpen']] != 'Closed'
                && $defaultValue[$header['tuesdayClose']] != '' && $defaultValue[$header['tuesdayClose']] != 'Closed') {
                $dayOfWeek = 'Tuesday';
                $open = $defaultValue[$header['tuesdayOpen']];
                $close = $defaultValue[$header['tuesdayClose']];

                $this->findOrCreateOperationHour($dayOfWeek, $open, $close, $city);
            }

            if ($defaultValue[$header['wednesdayOpen']] != '' && $defaultValue[$header['wednesdayOpen']] != 'Closed'
                && $defaultValue[$header['wednesdayClose']] != '' && $defaultValue[$header['wednesdayClose']] != 'Closed') {
                $dayOfWeek = 'Wednesday';
                $open = $defaultValue[$header['wednesdayOpen']];
                $close = $defaultValue[$header['wednesdayClose']];

                $this->findOrCreateOperationHour($dayOfWeek, $open, $close, $city);
            }

            if ($defaultValue[$header['thursdayOpen']] != '' && $defaultValue[$header['thursdayOpen']] != 'Closed'
                && $defaultValue[$header['thursdayClose']] != '' && $defaultValue[$header['thursdayClose']] != 'Closed') {
                $dayOfWeek = 'Thursday';
                $open = $defaultValue[$header['thursdayOpen']];
                $close = $defaultValue[$header['thursdayClose']];

                $this->findOrCreateOperationHour($dayOfWeek, $open, $close, $city);
            }

            if ($defaultValue[$header['fridayOpen']] != '' && $defaultValue[$header['fridayOpen']] != 'Closed'
                && $defaultValue[$header['fridayClose']] != '' && $defaultValue[$header['fridayClose']] != 'Closed') {
                $dayOfWeek = 'Friday';
                $open = $defaultValue[$header['fridayOpen']];
                $close = $defaultValue[$header['fridayClose']];

                $this->findOrCreateOperationHour($dayOfWeek, $open, $close, $city);
            }

            if ($defaultValue[$header['saturdayOpen']] != '' && $defaultValue[$header['saturdayOpen']] != 'Closed'
                && $defaultValue[$header['saturdayClose']] != '' && $defaultValue[$header['saturdayClose']] != 'Closed') {
                $dayOfWeek = 'Saturday';
                $open = $defaultValue[$header['saturdayOpen']];
                $close = $defaultValue[$header['saturdayClose']];

                $this->findOrCreateOperationHour($dayOfWeek, $open, $close, $city);
            }

            if ($defaultValue[$header['sundayOpen']] != '' && $defaultValue[$header['sundayOpen']] != 'Closed'
                && $defaultValue[$header['sundayClose']] != '' && $defaultValue[$header['sundayClose']] != 'Closed') {
                $dayOfWeek = 'Sunday';
                $open = $defaultValue[$header['sundayOpen']];
                $close = $defaultValue[$header['sundayClose']];

                $this->findOrCreateOperationHour($dayOfWeek, $open, $close, $city);
            }

            if ($defaultValue[$header['timezone']] != '') {
                $city->setTimezone($defaultValue[$header['timezone']]);
            }

            if ($defaultValue[$header['timezoneSummer']] != '') {
                $city->setTimezoneSummer($defaultValue[$header['timezoneSummer']]);
            }

            if ($defaultValue[$header['hoursDescriptionOther']] != '') {
                $city->setHoursDescriptionOther($defaultValue[$header['hoursDescriptionOther']]);
            }

            if ($defaultValue[$header['hoursDescription']] != '') {
                $city->setHoursDescription($defaultValue[$header['hoursDescription']]);
            }
            /* End Operation Hours */

            $city->setProfileAddedDate(new \DateTime('now', new \DateTimeZone('UTC')));

            $this->em->persist($city);
            $this->em->flush();
        }
        return true;
    }

    public function findOrCreateURL($type, $urlValue, $city){
        /* @var $urlTypeRepository UrlTypeRepository */
        $urlTypeRepository = $this->em->getRepository(UrlType::class);

        /* @var $urlRepository UrlRepository */
        $urlRepository = $this->em->getRepository(Url::class);


        if ($urlValue != '') {

            $urlTypeSlug = strtolower($type);
            $urlTypeSlug = preg_replace("/[^a-z0-9_\s-]/", "", $urlTypeSlug);
            $urlTypeSlug = preg_replace("/[\s-]+/", " ", $urlTypeSlug);
            $urlTypeSlug = preg_replace("/[\s_]/", "-", $urlTypeSlug);


            $urlType = $urlTypeRepository->findOneBy([
                'slug' => $urlTypeSlug
            ]);

            $url = $urlRepository->findOneBy([
                'city' => $city,
                'type' => $urlType
            ]);

            if($url == null){
                $url = new Url();

                if($urlType == null){
                    $urlType = new UrlType();
                    $urlType->setName($type);

                    $this->em->persist($urlType);
                }

                $url->setType($urlType);
                $url->setCity($city);

                $city->addUrl($url);
            }

            $url->setValue($urlValue);
            $this->em->persist($url);
        }
    }

    public function findOrCreateOperationHour($dayOfWeek, $open, $close, $city){
        /* @var $operationHoursRepository OperationHoursRepository */
        $operationHoursRepository = $this->em->getRepository(OperationHours::class);

        if ($dayOfWeek != '') {

            $dayOfWeek = strtolower($dayOfWeek);
            $operationHour = $operationHoursRepository->findOneBy([
                'city' => $city,
                'day' => $dayOfWeek
            ]);

            if($operationHour == null){
                $operationHour = new OperationHours();
                $operationHour->setDay($dayOfWeek);
                $operationHour->setCity($city);

                $city->addOperationHour($operationHour);
            }

            $openFormat = \DateTime::createFromFormat('Hi', str_pad($open, 4, '0', STR_PAD_LEFT));
            $closeFormat = \DateTime::createFromFormat('Hi', str_pad($close, 4, '0', STR_PAD_LEFT));

            $operationHour->setOpen($openFormat);
            $operationHour->setClose($closeFormat);

            $this->em->persist($operationHour);

        }
    }

}