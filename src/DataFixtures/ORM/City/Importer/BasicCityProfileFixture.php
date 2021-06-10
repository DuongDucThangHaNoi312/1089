<?php

namespace App\DataFixtures\ORM\City\Importer;

use App\DataFixtures\ORM\ImporterFixture;
use App\Service\CityProfileUploadImporter;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class BasicCityProfileFixture extends ImporterFixture implements DependentFixtureInterface {

    /**
     * @var CityProfileUploadImporter
     */
    private $importer;

    public function __construct(CityProfileUploadImporter $importer)
    {
        $this->importer = $importer;
    }

    public function getFileName()
    {
        return "BasicCityProfileFixture.csv";
    }

    public function getBasePath()
    {
        return parent::getBasePath() . 'City/Importer/';
    }

    public function import($file) {
        $this->importer->import($file);
    }

    public function getDependencies()
    {
        return array(
            InitialCityFixture::class,
        );
    }

}