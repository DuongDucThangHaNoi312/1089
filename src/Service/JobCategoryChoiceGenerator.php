<?php

namespace App\Service;

use App\Repository\JobTitle\Lookup\JobCategoryRepository;

class JobCategoryChoiceGenerator {

    /** @var JobCategoryRepository $repository */
    private $repository;

    public function __construct(JobCategoryRepository $repository)
    {
        $this->repository = $repository;
    }

    public function generate() {
        $list = [];
        $results = $this->repository->findBy([], ['name' => 'ASC']);
        foreach ($results as $jobCategory) {
            $list[$jobCategory->getName()] = $jobCategory->getId();
        }
        return $list;
    }

}