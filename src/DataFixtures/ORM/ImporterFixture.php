<?php

namespace App\DataFixtures\ORM;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

abstract class ImporterFixture extends Fixture
{
    /**
     * CSV filepath
     * @var string $csv
     */
    private $csv;

    abstract public function getFileName();

    /**
     * @param array|bool $file
     */
    abstract public function import($file);

    /**
     * Set CSV filepath
     * @param $filepath
     */
    public function setCSV($filepath)
    {
        $this->csv = $filepath;
    }

    /**
     * Get CSV filepath
     * @return string
     */
    public function getCSV() {
        return $this->csv;
    }

    public function getBasePath() {
        return __DIR__ . '/../CSV/';
    }

    public function load(ObjectManager $manager)
    {
        $this->setCSV($this->getBasePath() . $this->getFileName());
        if ($this->getCSV()) {
            $file = file($this->getCSV());
            $values = array_map('str_getcsv', $file);
            $this->import($values);
        }
    }
}