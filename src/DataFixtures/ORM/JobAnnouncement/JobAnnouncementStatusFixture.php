<?php

namespace App\DataFixtures\ORM\JobAnnouncement;

use App\DataFixtures\ORM\BaseFixture;
use App\Entity\CityRegistration\Lookup\CityRegistrationStatus;
use App\Entity\JobAnnouncement\Lookup\JobAnnouncementStatus;

class JobAnnouncementStatusFixture extends BaseFixture {

    public function getFileName()
    {
        return "JobAnnouncementStatusFixture.csv";
    }

    public function getObject()
    {
        return JobAnnouncementStatus::class;
    }

    public function getBasePath()
    {
        return parent::getBasePath() . 'JobAnnouncement/';
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