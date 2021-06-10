<?php

namespace App\Service;

use App\Repository\City\JobTitleRepository;

class JobTitleChoiceGenerator {

    /** @var JobTitleRepository $repository */
    private $repository;

    public function __construct(JobTitleRepository $repository)
    {
        $this->repository = $repository;
    }

    public function generate() {
        $list = [];
        $results = $this->repository->findBy([], ['jobTitleName' => 'ASC']);
        foreach ($results as $jobTitle) {
            $list[$jobTitle->getName()] = $jobTitle->getId();
        }
        return $list;
    }

}