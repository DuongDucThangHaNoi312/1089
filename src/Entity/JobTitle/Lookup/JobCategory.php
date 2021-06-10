<?php

namespace App\Entity\JobTitle\Lookup;

use App\Entity\City\JobTitle;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="App\Repository\JobTitle\Lookup\JobCategoryRepository")
 */
class JobCategory
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @var string
     * @Gedmo\Slug(fields={"name"}, updatable=true)
     * @ORM\Column(type="string", length=255)
     */
    private $slug;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\City\JobTitle", mappedBy="category")
     */
    private $jobTitles;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isGeneral = false;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $usedForSimilarSearch;

    public function __construct()
    {
        $this->jobTitles = new ArrayCollection();
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
     * @return JobCategory
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get slug.
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set slug.
     *
     * @param string $slug
     *
     * @return JobCategory
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

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
            $jobTitle->addCategory($this);
        }

        return $this;
    }

    public function removeJobTitle(JobTitle $jobTitle): self
    {
        if ($this->jobTitles->contains($jobTitle)) {
            $this->jobTitles->removeElement($jobTitle);
            $jobTitle->removeCategory($this);
        }

        return $this;
    }

    public function getIsGeneral(): ?bool
    {
        return $this->isGeneral;
    }

    public function setIsGeneral(bool $isGeneral): self
    {
        $this->isGeneral = $isGeneral;

        return $this;
    }

    public function getUsedForSimilarSearch(): ?bool
    {
        return $this->usedForSimilarSearch;
    }

    public function setUsedForSimilarSearch(?bool $usedForSimilarSearch): self
    {
        $this->usedForSimilarSearch = $usedForSimilarSearch;

        return $this;
    }
}
