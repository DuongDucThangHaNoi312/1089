<?php

namespace App\Service;

use App\Entity\City;
use App\Repository\CityRepository;

class CityChoiceGenerator {

    /** @var CityRepository $repository */
    private $repository;

    public function __construct(CityRepository $repository)
    {
        $this->repository = $repository;
    }

    public function generate($plainName = false) {
        $list = [];
        $results = $this->repository->findForCitySearch();
        foreach ($results as $city) {
            if ($plainName) {
                $list[$city['name']] = $city['id'];
            } else {
                $list[$city['cityString']] = $city['id'];
            }
        }
        return $list;
    }

    public function getLabel(City $city, City\County $county) {
        return $city->getDisplayName($county);
    }
}