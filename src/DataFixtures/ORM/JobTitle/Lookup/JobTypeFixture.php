<?php

namespace App\DataFixtures\ORM\JobTitle\Lookup;

use App\DataFixtures\ORM\BaseFixture;
use App\Entity\JobTitle\Lookup\JobType;

class JobTypeFixture extends BaseFixture {

    public function getFileName()
    {
        return "JobTypeFixture.csv";
    }

    public function getObject()
    {
        return JobType::class;
    }

    public function getBasePath()
    {
        return parent::getBasePath() . 'JobTitle/Lookup/';
    }

    /**
     * @param mixed|JobType $object
     * @param array $value
     * @param array $header
     */
    public function create(&$object, $value = array(), $header = array())
    {
        $object->setName($value[$header['name']]);
    }
}