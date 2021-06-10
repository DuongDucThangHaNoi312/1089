<?php

namespace App\Entity\City;

use App\Entity\City;
use App\Entity\SubscriptionPlan\CitySubscriptionPlan;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\User\Subscription as BaseSubscription;

/**
 * @ORM\Entity(repositoryClass="App\Repository\City\SubscriptionRepository")
 */
class Subscription extends BaseSubscription
{

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\City", inversedBy="subscription", cascade={"persist", "remove"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $city;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\SubscriptionPlan\CitySubscriptionPlan")
     * @ORM\JoinColumn(nullable=false)
     */
    private $subscriptionPlan;

    public function getCity(): ?City
    {
        return $this->city;
    }

    public function setCity(?City $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getSubscriptionPlan(): ?CitySubscriptionPlan
    {
        return $this->subscriptionPlan;
    }

    public function setSubscriptionPlan(?CitySubscriptionPlan $subscriptionPlan): self
    {
        $this->subscriptionPlan = $subscriptionPlan;

        return $this;
    }
}
