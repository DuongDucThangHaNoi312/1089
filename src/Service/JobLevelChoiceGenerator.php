<?php

namespace App\Service;

use App\Repository\JobTitle\Lookup\JobLevelRepository;

class JobLevelChoiceGenerator {

    /** @var JobLevelRepository $repository */
    private $repository;

    public function __construct(JobLevelRepository $repository)
    {
        $this->repository = $repository;
    }

    public function generate() {
        $list = [];
        $results = $this->repository->findBy([], ['name' => 'ASC']);
        foreach ($results as $jobLevel) {
            $list[$jobLevel->getName()] = $jobLevel->getId();
        }
        return $list;
    }

}