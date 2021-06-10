<?php

namespace App\DataFixtures\ORM\JobTitle\Lookup;

use App\DataFixtures\ORM\BaseFixture;
use App\Entity\JobTitle\Lookup\JobLevel;

class JobLevelFixture extends BaseFixture {

    public function getFileName()
    {
        return "JobLevelFixture.csv";
    }

    public function getObject()
    {
        return JobLevel::class;
    }

    public function getBasePath()
    {
        return parent::getBasePath() . 'JobTitle/Lookup/';
    }

    /**
     * @param mixed|JobLevel $object
     * @param array $value
     * @param array $header
     */
    public function create(&$object, $value = array(), $header = array())
    {
        $object->setName($value[$header['name']]);
        $object->setDescription($value[$header['description']]);
    }
}