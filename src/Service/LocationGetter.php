<?php

namespace App\Service;

use App\Entity\City;
use App\Entity\City\County;
use App\Entity\City\State;
use App\Entity\User\JobSeekerUser;
use Doctrine\ORM\EntityManagerInterface;

class LocationGetter
{
    private $em;

    /**
     * LocationGetter constructor.
     *
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @param $id
     * @param $entity
     *
     * @return null|object
     */
    public function getLocation($id, $entity)
    {
        $repository = $this->em->getRepository($entity);
        $data       = $repository->find($id);

        return $data;
    }

    /**
     * @param JobSeekerUser $jobSeeker
     * @param $locationString
     * @throws \Exception
     */
    public function setJobSeekerLocation(JobSeekerUser $jobSeeker, $locationString)
    {
        $dataLocations  = explode('_', $locationString);
        $city   = $this->getLocation($dataLocations[0], City::class);
        $county = $this->getLocation($dataLocations[1], County::class);
        $state  = $county ? $this->getLocation($county->getState()->getId(), State::class) : null;

        if ($city) {
            $jobSeeker->setCity($city);
        }

        if ($county) {
            $jobSeeker->setCounty($county);
        }

        if ($state) {
            $jobSeeker->setState($state);
        } else {
            throw new \Exception('Job Seeker must have a State!');
        }
    }

    /**
     * @param JobSeekerUser $jobSeeker
     * @param $locationString
     */
    public function setJobSeekerWorksForLocation(JobSeekerUser $jobSeeker, $locationString)
    {
        $dataLocations  = explode('_', $locationString);
        $city   = $this->getLocation($dataLocations[0], City::class);
        $county = $this->getLocation($dataLocations[1], County::class);

        if ($city) {
            $jobSeeker->setWorksForCity($city);
        }

        if ($county) {
            $jobSeeker->setWorksForCounty($county);
        }
    }
}