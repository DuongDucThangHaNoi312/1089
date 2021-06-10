<?php

namespace App\DataFixtures\ORM;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

abstract class MultilineBaseFixture extends Fixture {

    /**
     * CSV filepath
     * @var string $csv
     */
    private $csv;

    /**
     * Create object from a row of values
     * @param mixed $object
     * @param array $value
     * @param array $header
     */
    abstract public function create(&$object, $value = array(), $header = array());

    /**
     * Set FileName of CSV file
     * @return string
     */
    abstract public function getFileName();

    /**
     * Get class of Object being persisted
     * @return \stdClass
     */
    abstract public function getObject();

    /**
     * Set CSV filepath
     * @param $filepath
     */
    public function setCSV($filepath) {
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

    public function load(ObjectManager $manager) {
        $this->setCSV($this->getBasePath() . $this->getFileName());
        if ($this->getCSV()) {
            if (($handle = fopen($this->getCSV(), "r")) !== FALSE) {
                $header = fgetcsv($handle, 0, ",");
                $values = [];
                while (($row = fgetcsv($handle, 0, ",")) !== FALSE) {
                    $values[] = $row;
                }
                fclose($handle);
                $header = array_flip($header);
                $this->createObjects($manager, $header, $values);
            }
        }
    }

    /**
     * Create objects based on the values and header values of the CSV
     *
     * @param ObjectManager $manager
     * @param array $header
     * @param array $values
     *
     * @throws \ReflectionException
     */
    private function createObjects(ObjectManager $manager, $header = array(), $values = array()) {
        foreach ($values as $key => $value) {
            $r = new \ReflectionClass($this->getObject());
            $object = $r->newInstance();
            $this->create($object, $value, $header);
            $manager->persist($object);
        }
        $manager->flush();
    }
}