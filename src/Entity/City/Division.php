<?php

namespace App\Entity\City;

use App\Entity\City;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="App\Repository\City\DivisionRepository")
 */
class Division
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
     * @Gedmo\Slug(fields={"name"}, updatable=true)
     * @ORM\Column(type="string", length=255)
     */
    private $slug;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\City\Department", inversedBy="divisions")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $department;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\City", inversedBy="divisions")
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     */
    private $city;

    /**
     *
     * @ORM\OneToMany(targetEntity="App\Entity\City\JobTitle", mappedBy="division", cascade={"persist"})
     */
    private $jobTitles;

    public function __construct()
    {
        $this->jobTitles = new ArrayCollection();
    }

    public function __toString()
    {
        return (string) $this->getName();
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

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getDepartment(): ?Department
    {
        return $this->department;
    }

    public function setDepartment(?Department $department): self
    {
        $this->department = $department;

        return $this;
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

    /**
     * @return Collection|JobTitle[]
     */
    public function getJobTitles(): Collection
    {
        return $this->jobTitles;
    }

    public function addJobTitle(JobTitle $jobTitle): self
    {
        if (!$this->jobTitles->contains($jobTitle)) {
            $this->jobTitles[] = $jobTitle;
            $jobTitle->setDivision($this);
        }

        return $this;
    }

    public function removeJobTitle(JobTitle $jobTitle): self
    {
        if ($this->jobTitles->contains($jobTitle)) {
            $this->jobTitles->removeElement($jobTitle);
            // set the owning side to null (unless already changed)
            if ($jobTitle->getDivision() === $this) {
                $jobTitle->setDivision(null);
            }
        }

        return $this;
    }
}
