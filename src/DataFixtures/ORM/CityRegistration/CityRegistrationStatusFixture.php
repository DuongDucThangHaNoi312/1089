<?php

namespace App\DataFixtures\ORM\CityRegistration;

use App\DataFixtures\ORM\BaseFixture;
use App\Entity\CityRegistration\Lookup\CityRegistrationStatus;
use Doctrine\Common\Persistence\ObjectManager;

class CityRegistrationStatusFixture extends BaseFixture {

    public function getFileName()
    {
        return "CityRegistrationStatusFixture.csv";
    }

    public function getObject()
    {
        return CityRegistrationStatus::class;
    }

    public function getBasePath()
    {
        return parent::getBasePath() . 'CityRegistration/';
    }

    /**
     * @param mixed|CityRegistrationStatus $object
     * @param array $value
     * @param array $header
     */
    public function create(&$object, $value = array(), $header = array())
    {
        $object->setName($value[$header['name']]);
    }
}