<?php

namespace App\DataFixtures\ORM\JobAnnouncement;

use App\DataFixtures\ORM\BaseFixture;
use App\Entity\JobAnnouncement\Lookup\WageSalaryUnit;

class WageSalaryUnitFixture extends BaseFixture {

    public function getFileName()
    {
        return "WageSalaryUnitFixture.csv";
    }

    public function getObject()
    {
        return WageSalaryUnit::class;
    }

    public function getBasePath()
    {
        return parent::getBasePath() . 'JobAnnouncement/';
    }

    /**
     * @param mixed|WageSalaryUnit $object
     * @param array $value
     * @param array $header
     */
    public function create(&$object, $value = array(), $header = array())
    {
        $object->setName($value[$header['name']]);
    }
}