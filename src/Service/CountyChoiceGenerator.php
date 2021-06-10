<?php

namespace App\Service;

use App\Repository\City\CountyRepository;

class CountyChoiceGenerator {

    /** @var CountyRepository $repository */
    private $repository;

    public function __construct(CountyRepository $repository)
    {
        $this->repository = $repository;
    }

    public function generate() {
        $list = [];
        $results = $this->repository->findByStateIsActive();
        foreach ($results as $county) {
            $list[$county->getName()] = $county->getId();
        }
        return $list;
    }

}