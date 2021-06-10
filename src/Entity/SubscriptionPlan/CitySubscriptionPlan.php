<?php

namespace App\Entity\SubscriptionPlan;

use App\Entity\City;
use App\Entity\SubscriptionPlan;
use App\Entity\SubscriptionPlan\PriceSchedule;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SubscriptionPlan\CitySubscriptionPlanRepository")
 */
class CitySubscriptionPlan extends SubscriptionPlan
{

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $isTrial = false;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $allowedChangeHideExecutiveSeniorJobLevelPositions = false;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $allowedActiveJobPostings;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $hasJobTitleMaintenanceRequirement;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    private $jobTitleMaintenancePercentage;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $countOfAllowedUsers;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $jobsOfInterestStars;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $hasSearchResumeLimitation;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $hasSearchCityLinksLimitation;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\SubscriptionPlan\PriceSchedule", mappedBy="subscriptionPlan", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $priceSchedules;

    public function getPriceByFTE(int $fte = 0) {
        if (count($this->getPriceSchedules()) == 0) {
            return $this->getPrice();
        }

        foreach ($this->getPriceSchedules() as $priceSchedule) {
            if ($priceSchedule->getMinCountOfFTEs() <= $fte && $priceSchedule->getMaxCountOfFTEs() >= $fte) {
                return $priceSchedule->getPrice();
            }
        }

        return $this->getPrice();
    }

    public function isCityCompliant(City $city) {
        $requiredVisiblePercent = $this->getJobTitleMaintenancePercentage();
        $wouldBeVisiblePercent = $city->getWouldBePercentageJobTitlesVisible(0);

        if (($wouldBeVisiblePercent*100) < $requiredVisiblePercent) {
            return false;
        }
        return true;
    }

    public function getNextPriceByFTE(int $fte = 0) {
        if (count($this->getPriceSchedules()) == 0) {
            return $this->getNextPrice();
        }

        foreach ($this->getPriceSchedules() as $priceSchedule) {
            if ($priceSchedule->getMinCountOfFTEs() <= $fte && $priceSchedule->getMaxCountOfFTEs() >= $fte) {
                return $priceSchedule->getNextPrice();
            }
        }

        return $this->getNextPrice();
    }

    public function getNextPriceEffectiveDateByFTE(int $fte = 0) {
        if (count($this->getPriceSchedules()) == 0) {
            return $this->getNextPriceEffectiveDate();
        }

        foreach ($this->getPriceSchedules() as $priceSchedule) {
            if ($priceSchedule->getMinCountOfFTEs() <= $fte && $priceSchedule->getMaxCountOfFTEs() >= $fte) {
                return $priceSchedule->getNextPriceEffectiveDate();
            }
        }

        return $this->getNextPriceEffectiveDate();
    }

    public function getPriceScheduleByFTE(int $fte = 0) {
        foreach ($this->getPriceSchedules() as $priceSchedule) {
            if ($priceSchedule->getMinCountOfFTEs() <= $fte && $priceSchedule->getMaxCountOfFTEs() >= $fte) {
                return $priceSchedule;
            }
        }

        return null;
    }

    public function __construct()
    {
        $this->priceSchedules = new ArrayCollection();
    }

    public function getAllowedActiveJobPostings(): ?int
    {
        return $this->allowedActiveJobPostings;
    }

    public function setAllowedActiveJobPostings(?int $allowedActiveJobPostings): self
    {
        $this->allowedActiveJobPostings = $allowedActiveJobPostings;

        return $this;
    }

    public function getAllowedChangeHideExecutiveSeniorJobLevelPositions() : ?bool {
        return $this->allowedChangeHideExecutiveSeniorJobLevelPositions;
    }

    public function setAllowedChangeHideExecutiveSeniorJobLevelPositions(?bool $allowedChangeHideExecutiveSeniorJobLevelPositions): self
    {
        $this->allowedChangeHideExecutiveSeniorJobLevelPositions = $allowedChangeHideExecutiveSeniorJobLevelPositions;

        return $this;
    }

    public function getHasJobTitleMaintenanceRequirement(): ?bool
    {
        return $this->hasJobTitleMaintenanceRequirement;
    }

    public function setHasJobTitleMaintenanceRequirement(?bool $hasJobTitleMaintenanceRequirement): self
    {
        $this->hasJobTitleMaintenanceRequirement = $hasJobTitleMaintenanceRequirement;

        return $this;
    }

    public function getJobTitleMaintenancePercentage()
    {
        return $this->jobTitleMaintenancePercentage;
    }

    public function setJobTitleMaintenancePercentage($jobTitleMaintenancePercentage): self
    {
        $this->jobTitleMaintenancePercentage = $jobTitleMaintenancePercentage;

        return $this;
    }

    public function getCountOfAllowedUsers(): ?int
    {
        return $this->countOfAllowedUsers;
    }

    public function setCountOfAllowedUsers(?int $countOfAllowedUsers): self
    {
        $this->countOfAllowedUsers = $countOfAllowedUsers;

        return $this;
    }

    public function getJobsOfInterestStars(): ?int
    {
        return $this->jobsOfInterestStars;
    }

    public function setJobsOfInterestStars(?int $jobsOfInterestStars): self
    {
        $this->jobsOfInterestStars = $jobsOfInterestStars;

        return $this;
    }

    public function getHasSearchResumeLimitation(): ?bool
    {
        return $this->hasSearchResumeLimitation;
    }

    public function setHasSearchResumeLimitation(?bool $hasSearchResumeLimitation): self
    {
        $this->hasSearchResumeLimitation = $hasSearchResumeLimitation;

        return $this;
    }

    public function getHasSearchCityLinksLimitation(): ?bool
    {
        return $this->hasSearchCityLinksLimitation;
    }

    public function setHasSearchCityLinksLimitation(?bool $hasSearchCityLinksLimitation): self
    {
        $this->hasSearchCityLinksLimitation = $hasSearchCityLinksLimitation;

        return $this;
    }

    /**
     * @return Collection|PriceSchedule[]
     */
    public function getPriceSchedules(): Collection
    {
        return $this->priceSchedules;
    }

    public function addPriceSchedule(PriceSchedule $priceSchedule): self
    {
        if (!$this->priceSchedules->contains($priceSchedule)) {
            $this->priceSchedules[] = $priceSchedule;
            $priceSchedule->setSubscriptionPlan($this);
        }

        return $this;
    }

    public function removePriceSchedule(PriceSchedule $priceSchedule): self
    {
        if ($this->priceSchedules->contains($priceSchedule)) {
            $this->priceSchedules->removeElement($priceSchedule);
            // set the owning side to null (unless already changed)
            if ($priceSchedule->getSubscriptionPlan() === $this) {
                $priceSchedule->setSubscriptionPlan(null);
            }
        }

        return $this;
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


}
