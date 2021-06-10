<?php

namespace App\DataFixtures\ORM\City\Importer;

use App\DataFixtures\ORM\ImporterFixture;
use App\Service\JobTitleUploadImporter;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class JobTitleFixture extends ImporterFixture implements DependentFixtureInterface {

    /**
     * @var JobTitleUploadImporter
     */
    private $importer;

    public function __construct(JobTitleUploadImporter $importer)
    {
        $this->importer = $importer;
    }

    public function getFileName()
    {
        return "JobTitleFixture.csv";
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
            BasicCityProfileFixture::class,
        );
    }

}