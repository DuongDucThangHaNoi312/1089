<?php

namespace App\Entity;

use App\Entity\User\CityUser;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CityCityUserRepository")
 */
class CityCityUser
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\City", inversedBy="cityCityUsers")
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     */
    private $city;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User\CityUser")
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     */
    private $cityUser;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCity(): ?City
    {
        return $this->city;
    }

    public function setCity(?City $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getCityUser(): ?CityUser
    {
        return $this->cityUser;
    }

    public function setCityUser(?CityUser $cityUser): self
    {
        $this->cityUser = $cityUser;

        return $this;
    }
}
