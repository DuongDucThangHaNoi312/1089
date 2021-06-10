<?php

namespace App\Entity\JobTitle\Lookup;

use App\Entity\City;
use App\Entity\City\JobTitle;
use App\Traits\BlameableEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\JobTitle\Lookup\JobTitleNameRepository")
 */
class JobTitleName
{

    use TimestampableEntity;
    use BlameableEntity;

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
     * @ORM\OneToMany(targetEntity="App\Entity\City\JobTitle", mappedBy="jobTitleName")
     */
    private $jobTitles;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\City", inversedBy="createdJobTitleNames")
     */
    private $createdByCity;

    public function __toString()
    {
        return (string) $this->getName();
    }

    public function __construct()
    {
        $this->jobTitles = new ArrayCollection();
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
            $jobTitle->setJobTitleName($this);
        }

        return $this;
    }

    public function removeJobTitle(JobTitle $jobTitle): self
    {
        if ($this->jobTitles->contains($jobTitle)) {
            $this->jobTitles->removeElement($jobTitle);
            // set the owning side to null (unless already changed)
            if ($jobTitle->getJobTitleName() === $this) {
                $jobTitle->setJobTitleName(null);
            }
        }

        return $this;
    }

    public function getCreatedByCity(): ?City
    {
        return $this->createdByCity;
    }

    public function setCreatedByCity(?City $createdByCity): self
    {
        $this->createdByCity = $createdByCity;

        return $this;
    }
}
