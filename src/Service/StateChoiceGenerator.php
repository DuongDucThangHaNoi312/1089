<?php

namespace App\Service;

use App\Repository\City\StateRepository;

class StateChoiceGenerator {

    /** @var StateRepository $repository */
    private $repository;

    public function __construct(StateRepository $repository)
    {
        $this->repository = $repository;
    }

    public function generate() {
        $list = [];
        $results = $this->repository->findBy(['isActive' => true], ['name' => 'ASC']);
        foreach ($results as $state) {
            $list[$state->getName()] = $state->getId();
        }
        return $list;
    }

}