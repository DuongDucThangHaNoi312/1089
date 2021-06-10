<?php

namespace App\DataFixtures\ORM\SubscriptionPlan;

use App\DataFixtures\ORM\BaseFixture;
use App\Entity\SubscriptionPlan\Lookup\RenewalFrequency;

class RenewalFrequencyFixture extends BaseFixture {

    public function getFileName()
    {
        return "RenewalFrequencyFixture.csv";
    }

    public function getObject()
    {
        return RenewalFrequency::class;
    }

    public function getBasePath()
    {
        return parent::getBasePath() . "SubscriptionPlan/";
    }

    /**
     * @param mixed|RenewalFrequency $object
     * @param array $value
     * @param array $header
     */
    public function create(&$object, $value = array(), $header = array())
    {
        $object->setName($value[$header['name']]);
    }
}