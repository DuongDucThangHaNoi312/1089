<?php

namespace App\Entity\City;

use App\Entity\City;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\City\CensusPopulationRepository")
 */
class CensusPopulation
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $year;

    /**
     * @ORM\Column(type="integer")
     */
    private $population;

    /**
     * @var \App\Entity\City
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\City", inversedBy="censusPopulations")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $city;

    public function __toString()
    {
        return (string) $this->getYear();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getYear(): ?int
    {
        return $this->year;
    }

    public function setYear(int $year): self
    {
        $this->year = $year;

        return $this;
    }

    public function getPopulation(): ?int
    {
        return $this->population;
    }

    public function setPopulation(int $population): self
    {
        $this->population = $population;

        return $this;
    }

    public function getCity(): ?\App\Entity\City
    {
        return $this->city;
    }

    public function setCity(?\App\Entity\City $city): self
    {
        $this->city = $city;

        return $this;
    }
}
