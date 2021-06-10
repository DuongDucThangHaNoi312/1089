<?php

namespace App\Entity\SubscriptionPlan;

use App\Entity\SubscriptionPlan;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SubscriptionPlan\PriceScheduleRepository")
 */
class PriceSchedule
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\SubscriptionPlan\CitySubscriptionPlan", inversedBy="priceSchedules")
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     */
    private $subscriptionPlan;

    /**
     * @var float
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $price;

    /**
     * @ORM\Column(type="decimal", precision=5, scale=2, nullable=true)
     */
    private $nextPrice;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="date", nullable=true)
     */
    private $nextPriceEffectiveDate;

    /**
     * @ORM\Column(type="integer")
     */
    private $minCountOfFTEs;

    /**
     * @ORM\Column(type="integer")
     */
    private $maxCountOfFTEs;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $rawStripePlan;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPrice()
    {
        return $this->price;
    }

    public function setPrice($price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getNextPrice()
    {
        return $this->nextPrice;
    }

    public function setNextPrice($nextPrice): self
    {
        $this->nextPrice = $nextPrice;

        return $this;
    }

    public function getMinCountOfFTEs(): ?int
    {
        return $this->minCountOfFTEs;
    }

    public function setMinCountOfFTEs(int $minCountOfFTEs): self
    {
        $this->minCountOfFTEs = $minCountOfFTEs;

        return $this;
    }

    public function getMaxCountOfFTEs(): ?int
    {
        return $this->maxCountOfFTEs;
    }

    public function setMaxCountOfFTEs(int $maxCountOfFTEs): self
    {
        $this->maxCountOfFTEs = $maxCountOfFTEs;

        return $this;
    }

    public function getSubscriptionPlan(): ?SubscriptionPlan
    {
        return $this->subscriptionPlan;
    }

    public function setSubscriptionPlan(?SubscriptionPlan $subscriptionPlan): self
    {
        $this->subscriptionPlan = $subscriptionPlan;

        return $this;
    }

    public function getNextPriceEffectiveDate(): ?\DateTimeInterface
    {
        return $this->nextPriceEffectiveDate;
    }

    public function setNextPriceEffectiveDate(?\DateTimeInterface $nextPriceEffectiveDate): self
    {
        $this->nextPriceEffectiveDate = $nextPriceEffectiveDate;

        return $this;
    }

    public function getRawStripePlan(): ?string
    {
        return $this->rawStripePlan;
    }

    public function setRawStripePlan(string $rawStripePlan): self
    {
        $this->rawStripePlan = $rawStripePlan;

        return $this;
    }

}
