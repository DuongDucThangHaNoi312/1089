<?php

namespace App\Entity\User;

use App\Entity\SubscriptionPlan;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\User\SubscriptionChangeRequestRepository")
 */
class SubscriptionChangeRequest
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $changeOn;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\User\Subscription", inversedBy="subscriptionChangeRequest", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $subscription;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\SubscriptionPlan")
     * @ORM\JoinColumn(nullable=false)
     */
    private $newSubscriptionPlan;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\SubscriptionPlan")
     * @ORM\JoinColumn(nullable=false)
     */
    private $oldSubscriptionPlan;

    /**
     * @ORM\Column(type="datetime")
     */
    private $requestedAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getChangeOn(): ?\DateTimeInterface
    {
        return $this->changeOn;
    }

    public function setChangeOn(\DateTimeInterface $changeOn): self
    {
        $this->changeOn = $changeOn;

        return $this;
    }

    public function getSubscription(): ?Subscription
    {
        return $this->subscription;
    }

    public function setSubscription(Subscription $subscription): self
    {
        $this->subscription = $subscription;

        return $this;
    }

    public function getNewSubscriptionPlan(): ?SubscriptionPlan
    {
        return $this->newSubscriptionPlan;
    }

    public function setNewSubscriptionPlan(SubscriptionPlan $newSubscriptionPlan): self
    {
        $this->newSubscriptionPlan = $newSubscriptionPlan;

        return $this;
    }

    public function getOldSubscriptionPlan(): ?SubscriptionPlan
    {
        return $this->oldSubscriptionPlan;
    }

    public function setOldSubscriptionPlan(SubscriptionPlan $oldSubscriptionPlan): self
    {
        $this->oldSubscriptionPlan = $oldSubscriptionPlan;

        return $this;
    }

    public function getRequestedAt(): ?\DateTimeInterface
    {
        return $this->requestedAt;
    }

    public function setRequestedAt(\DateTimeInterface $requestedAt): self
    {
        $this->requestedAt = $requestedAt;

        return $this;
    }
}
