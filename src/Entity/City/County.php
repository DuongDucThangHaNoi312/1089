<?php

namespace App\Entity\City;

use App\Entity\City;
use App\Entity\User\JobSeekerUser;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="App\Repository\City\CountyRepository")
 */
class County
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @var string
     * @Gedmo\Slug(fields={"name"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $slug;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isActive = false;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\City", inversedBy="counties", cascade={"remove", "persist"}, orphanRemoval=true)
     * @Orm\OrderBy({"name" = "ASC"})
     */
    private $cities;

    /**
     * @var \App\Entity\City\State
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\City\State", inversedBy="counties", cascade={"persist"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $state;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\User\JobSeekerUser", mappedBy="interestedCounties")
     */
    private $interestedJobSeekers;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $activateForCitySearch;

    public function __toString()
    {
        return (string) $this->getName();
    }

    public function getDisplayName() {
        return (string) $this->getName() . ", " . (string) $this->getState();
    }

    public function __construct()
    {
        $this->cities = new ArrayCollection();
        $this->interestedJobSeekers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection|City[]
     */
    public function getCities(): Collection
    {
        return $this->cities;
    }

    public function addCity(City $city): self
    {
        if (!$this->cities->contains($city)) {
            $this->cities[] = $city;
        }

        return $this;
    }

    public function removeCity(City $city): self
    {
        if ($this->cities->contains($city)) {
            $this->cities->removeElement($city);
        }

        return $this;
    }

    public function getState(): ?State
    {
        return $this->state;
    }

    public function setState(?State $state): self
    {
        $this->state = $state;

        return $this;
    }

    /**
     * @return Collection|JobSeekerUser[]
     */
    public function getInterestedJobSeekers(): Collection
    {
        return $this->interestedJobSeekers;
    }

    public function addInterestedJobSeeker(JobSeekerUser $interestedJobSeeker): self
    {
        if (!$this->interestedJobSeekers->contains($interestedJobSeeker)) {
            $this->interestedJobSeekers[] = $interestedJobSeeker;
        }

        return $this;
    }

    public function removeInterestedJobSeeker(JobSeekerUser $interestedJobSeeker): self
    {
        if ($this->interestedJobSeekers->contains($interestedJobSeeker)) {
            $this->interestedJobSeekers->removeElement($interestedJobSeeker);
        }

        return $this;
    }

    public function getIsActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getActivateForCitySearch(): ?bool
    {
        return $this->activateForCitySearch;
    }

    public function setActivateForCitySearch(?bool $activateForCitySearch): self
    {
        $this->activateForCitySearch = $activateForCitySearch;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }
}
