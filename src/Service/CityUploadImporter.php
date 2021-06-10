<?php

namespace App\Service;

use App\Entity\City;
use App\Entity\City\County;
use App\Entity\City\State;
use App\Repository\CityRepository;
use App\Repository\City\CountyRepository;
use App\Repository\City\StateRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Sonata\AdminBundle\Exception\ModelManagerException;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Validator\Constraints\Date;

class CityUploadImporter
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
            if($row[$header['cityName']] == '' && $row[$header['cityPrefix']] == '' && $row[$header['stateName']] == ''
                && $row[$header['stateAbbreviation']] == '' && $row[$header['county1Name']] == '' && $row[$header['county2Name']] == ''
                && $row[$header['county3Name']] == '' && $row[$header['county4Name']] == '' && $row[$header['county5Name']] == ''){
                unset($file[$index]);
            }
        }

        foreach ($file as $index=>$defaultValue) {

            if($defaultValue[$header['stateName']] == ''
                && $defaultValue[$header['cityName']] == ''
                && $defaultValue[$header['county1Name']] == ''){
                $this->session->getBag('flashes')->add('error', 'Every record must contain stateName, CityName & County1Name');
                return false;
            }

            /* @var $stateRepository StateRepository */
            $stateRepository = $this->em->getRepository(State::class);
            $state = $stateRepository->findOneBy([
                'name' => $defaultValue[$header['stateName']]
            ]);

            if($defaultValue[$header['stateName']] != '' && $state == null) {
                $state = new State();

                $state->setName($defaultValue[$header['stateName']]);

                if($defaultValue[$header['stateAbbreviation']] != '') {
                    $state->setAbbreviation($defaultValue[$header['stateAbbreviation']]);
                }

                $state->setIsActive(true);

                $this->em->persist($state);
                $this->em->flush($state);
            }

            $county1 = null;
            $county2 = null;
            $county3 = null;
            $county4 = null;
            $county5 = null;
            $city = null;

            if($defaultValue[$header['county1Name']] != '') {
                $countyName = $defaultValue[$header['county1Name']];

                $county1 = $this->findOrCreateCounty($state, $countyName);
            }

            if($defaultValue[$header['county2Name']] != '') {
                $countyName = $defaultValue[$header['county2Name']];

                $county2 = $this->findOrCreateCounty($state, $countyName);
            }

            if($defaultValue[$header['county3Name']] != '') {
                $countyName = $defaultValue[$header['county3Name']];

                $county3 = $this->findOrCreateCounty($state, $countyName);
            }

            if($defaultValue[$header['county4Name']] != '') {
                $countyName = $defaultValue[$header['county4Name']];

                $county4 = $this->findOrCreateCounty($state, $countyName);
            }

            if($defaultValue[$header['county5Name']] != '') {
                $countyName = $defaultValue[$header['county5Name']];

                $county5 = $this->findOrCreateCounty($state, $countyName);
            }

            if($defaultValue[$header['cityName']] != '') {
                $cityName = $defaultValue[$header['cityName']];

                if ($county1) {
                    $city = $this->findOrCreateCity($county1, $cityName);
                }

                if ($county2) {
                    $city->addCounty($county2);
                    $county2->addCity($city);

                    $this->em->persist($county2);
                }

                if ($county3) {
                    $city->addCounty($county3);
                    $county3->addCity($city);

                    $this->em->persist($county3);
                }

                if ($county4) {
                    $city->addCounty($county4);
                    $county4->addCity($city);

                    $this->em->persist($county4);
                }

                if ($county5) {
                    $city->addCounty($county5);
                    $county5->addCity($city);

                    $this->em->persist($county5);
                }

                if ($city && $defaultValue[$header['cityPrefix']] != '') {
                    $cityPrefix = $defaultValue[$header['cityPrefix']];

                    $city->setPrefix($cityPrefix);

                    $this->em->persist($city);
                }
            }

            $this->em->flush();
        }
        return true;
    }

    public function findOrCreateCounty($state, $countyName){
        /* @var $countyRepository CountyRepository */
        $countyRepository = $this->em->getRepository(County::class);


        if($countyName != '') {
            $county = $countyRepository->findOneBy([
                'name' => $countyName,
                'state' => $state,
            ]);

            if($state && $county) {
                $county->setState($state);
            }

            if($county == null) {
                $county = new County();

                $county->setName($countyName);
                $county->setState($state);

                $this->em->persist($county);
                $this->em->flush($county);
            }

            return $county;
        }
    }

    public function findOrCreateCity($county = null, $cityName){
        /* @var $cityRepository CityRepository */
        $cityRepository = $this->em->getRepository(City::class);

        if($cityName != '') {
            $city = $cityRepository->findOneByCounty($cityName, $county);

            if($city == null) {
                $city = new City();

                $city->setName($cityName);
            }

            if($county){
                $city->addCounty($county);
                $county->addCity($city);

                $this->em->persist($county);
            }
            $this->em->persist($city);

            return $city;
        }
    }

}