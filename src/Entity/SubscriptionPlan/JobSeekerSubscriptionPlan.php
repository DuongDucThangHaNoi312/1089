<?php

namespace App\Entity\SubscriptionPlan;

use App\Entity\JobTitle\Lookup\JobLevel;
use App\Entity\SubscriptionPlan;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SubscriptionPlan\JobSeekerSubscriptionPlanRepository")
 */
class JobSeekerSubscriptionPlan extends SubscriptionPlan
{

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $isTrial = false;

    /**
     * @var Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\JobTitle\Lookup\JobLevel")
     */
    private $allowedJobLevels;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $countSavedSearches;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $limitCityLinkSearchToCountyOfResidence;

    public function __construct()
    {
        $this->allowedJobLevels = new ArrayCollection();
    }

    public function getIsTrial(): ?bool
    {
        return $this->isTrial;
    }

    public function setIsTrial(bool $isTrial): self
    {
        $this->isTrial = $isTrial;

        return $this;
    }

    public function getCountSavedSearches(): ?int
    {
        return $this->countSavedSearches;
    }

    public function setCountSavedSearches(?int $countSavedSearches): self
    {
        $this->countSavedSearches = $countSavedSearches;

        return $this;
    }

    public function getLimitCityLinkSearchToCountyOfResidence(): ?bool
    {
        return $this->limitCityLinkSearchToCountyOfResidence;
    }

    public function setLimitCityLinkSearchToCountyOfResidence(?bool $limitCityLinkSearchToCountyOfResidence): self
    {
        $this->limitCityLinkSearchToCountyOfResidence = $limitCityLinkSearchToCountyOfResidence;

        return $this;
    }

    /**
     * @return Collection|JobLevel[]
     */
    public function getAllowedJobLevels(): Collection
    {
        return $this->allowedJobLevels;
    }

    public function addAllowedJobLevel(JobLevel $allowedJobLevel): self
    {
        if (!$this->allowedJobLevels->contains($allowedJobLevel)) {
            $this->allowedJobLevels[] = $allowedJobLevel;
        }

        return $this;
    }

    public function removeAllowedJobLevel(JobLevel $allowedJobLevel): self
    {
        if ($this->allowedJobLevels->contains($allowedJobLevel)) {
            $this->allowedJobLevels->removeElement($allowedJobLevel);
        }

        return $this;
    }

}
