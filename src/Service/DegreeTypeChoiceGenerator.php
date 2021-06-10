<?php

namespace App\Service;

use App\Entity\Resume\Education;
use App\Repository\Resume\Lookup\DegreeTypeRepository;

class DegreeTypeChoiceGenerator {

    /** @var DegreeTypeRepository $repository */
    private $repository;

    public function __construct(DegreeTypeRepository $repository)
    {
        $this->repository = $repository;
    }

    public function generate() {
        $list = [];
        $results = $this->repository->findBy([], ['name' => 'ASC']);
        foreach ($results as $degreeType) {
            $list[$degreeType->getName()] = $degreeType->getId();
        }
        return $list;
    }
}