<?php

namespace App\Entity\City;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="App\Repository\City\StateRepository")
 */
class State
{
    CONST CALIFORNIA_STATE = 'california';

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
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $abbreviation;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\City\County", mappedBy="state", cascade={"remove", "persist"})
     * @Orm\OrderBy({"name" = "ASC"})
     */
    private $counties;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isActive = false;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $activatedDate;

    public function __construct()
    {
        $this->counties = new ArrayCollection();
    }

    public function __toString()
    {
        return (string) $this->getName();
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return State
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get abbreviation.
     *
     * @return string
     */
    public function getAbbreviation()
    {
        return $this->abbreviation;
    }

    /**
     * Set abbreviation.
     *
     * @param string $abbreviation
     *
     * @return State
     */
    public function setAbbreviation($abbreviation)
    {
        $this->abbreviation = $abbreviation;

        return $this;
    }

    /**
     * Get isActive.
     *
     * @return bool
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * Set isActive.
     *
     * @param bool $isActive
     *
     * @return State
     */
    public function setIsActive($isActive)
    {
        $date = null;
        if ($isActive) {
            $date = new \DateTime();
        }
        $this->setActivatedDate($date);
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * Get counties.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCounties()
    {
        return $this->counties;
    }

    public function addCounty(County $county)
    {
        if (!$this->counties->contains($county)) {
            $this->counties[] = $county;
            $county->setState($this);
        }

        return $this;
    }

    public function removeCounty(County $county): self
    {
        if ($this->counties->contains($county)) {
            $this->counties->removeElement($county);
            // set the owning side to null (unless already changed)
            if ($county->getState() === $this) {
                $county->setState(null);
            }
        }

        return $this;
    }

    public function getActivatedDate(): ?\DateTimeInterface
    {
        return $this->activatedDate;
    }

    public function setActivatedDate(?\DateTimeInterface $activatedDate): self
    {
        $this->activatedDate = $activatedDate;

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
