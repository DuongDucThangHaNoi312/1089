<?php

namespace App\DataFixtures\ORM\City\Importer;

use App\DataFixtures\ORM\ImporterFixture;
use App\Service\CityUploadImporter;

class InitialCityFixture extends ImporterFixture {

    /**
     * @var CityUploadImporter
     */
    private $importer;

    public function __construct(CityUploadImporter $importer)
    {
        $this->importer = $importer;
    }

    public function getFileName()
    {
        return "InitialCityFixture.csv";
    }

    public function getBasePath()
    {
        return parent::getBasePath() . 'City/Importer/';
    }

    public function import($file) {
        $this->importer->import($file);
    }

}