<?php

namespace App\DataFixtures\ORM\JobTitle\Lookup;

use App\DataFixtures\ORM\BaseFixture;
use App\Entity\JobTitle\Lookup\JobCategory;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;

class JobCategoryFixture extends BaseFixture implements FixtureGroupInterface {

    public function getFileName()
    {
        return "JobCategoryFixture.csv";
    }

    public function getObject()
    {
        return JobCategory::class;
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
        $object->setSlug($value[$header['slug']]);
        $object->setIsGeneral($value[$header['is_general']]);
    }

    public static function getGroups(): array
    {
        return ['generalcats'];
    }
}