<?php

namespace App\DataFixtures\ORM\Resume;

use App\DataFixtures\ORM\BaseFixture;
use App\Entity\Resume\Lookup\DegreeType;

class DegreeTypeFixture extends BaseFixture {

    public function getFileName()
    {
        return "DegreeTypeFixture.csv";
    }

    public function getObject()
    {
        return DegreeType::class;
    }

    public function getBasePath()
    {
        return parent::getBasePath() . 'Resume/';
    }

    /**
     * @param mixed|DegreeType $object
     * @param array $value
     * @param array $header
     */
    public function create(&$object, $value = array(), $header = array())
    {
        $object->setName($value[$header['name']]);
    }
}