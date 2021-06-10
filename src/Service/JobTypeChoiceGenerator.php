<?php

namespace App\Service;

use App\Repository\JobTitle\Lookup\JobTypeRepository;

class JobTypeChoiceGenerator {

    /** @var JobTypeRepository $repository */
    private $repository;

    public function __construct(JobTypeRepository $repository)
    {
        $this->repository = $repository;
    }

    public function generate() {
        $list = [];
        $results = $this->repository->findBy([], ['name' => 'ASC']);
        foreach ($results as $jobType) {
            $list[$jobType->getName()] = $jobType->getId();
        }
        return $list;
    }

}