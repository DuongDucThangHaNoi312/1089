<?php

namespace App\Entity\User;

use App\Entity\Stripe\StripeSubscription;
use Doctrine\ORM\Mapping as ORM;
use App\Traits\BlameableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\User\SubscriptionRepository")
 *
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap(
 *     {
 *     "subscription" = "Subscription",
 *     "job-seeker-subscription" = "App\Entity\User\JobSeekerUser\Subscription",
 *     "city-subscription" = "App\Entity\City\Subscription",
 *     }
 * )
 */
class Subscription
{

    use BlameableEntity;
    use TimestampableEntity;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $expiresAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $cancelledAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $cancellationRequestedAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="date", nullable=true)
     */
    private $willCancelOn;

    /**
     * @var \App\Entity\Stripe\StripeSubscription
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Stripe\StripeSubscription")
     */
    private $stripeSubscription;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $rawStripeSubscription;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    private $isPaid = false;

    /**
     * @ORM\OneToOne(targetEntity="SubscriptionChangeRequest", mappedBy="subscription", cascade={"persist", "remove"})
     */
    private $subscriptionChangeRequest;

    public function getPaymentProcessorSubscriptionId() {
        $stripeSubscription = $this->getStripeSubscription();
        if ($stripeSubscription) {
            return $stripeSubscription->getStripeId();
        }

        if ($this->getRawStripeSubscription()) {
            return $this->getRawStripeSubscription();
        }

        return '';
    }

    public function isCancelled() {
        return $this->getCancelledAt() > $this->getExpiresAt();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getExpiresAt(): ?\DateTimeInterface
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(\DateTimeInterface $expiresAt): self
    {
        $this->expiresAt = $expiresAt;

        return $this;
    }

    public function getCancelledAt(): ?\DateTimeInterface
    {
        return $this->cancelledAt;
    }

    public function setCancelledAt(?\DateTimeInterface $cancelledAt): self
    {
        $this->cancelledAt = $cancelledAt;

        return $this;
    }

    public function getStripeSubscription(): ?\App\Entity\Stripe\StripeSubscription
    {
        return $this->stripeSubscription;
    }

    public function setStripeSubscription(?\App\Entity\Stripe\StripeSubscription $stripeSubscription): self
    {
        $this->stripeSubscription = $stripeSubscription;

        return $this;
    }

    public function getCancellationRequestedAt(): ?\DateTimeInterface
    {
        return $this->cancellationRequestedAt;
    }

    public function setCancellationRequestedAt(?\DateTimeInterface $cancellationRequestedAt): self
    {
        $this->cancellationRequestedAt = $cancellationRequestedAt;

        return $this;
    }

    public function getWillCancelOn(): ?\DateTimeInterface
    {
        return $this->willCancelOn;
    }

    public function setWillCancelOn(?\DateTimeInterface $willCancelOn): self
    {
        $this->willCancelOn = $willCancelOn;

        return $this;
    }

    public function getIsPaid(): bool {
        return $this->isPaid;
    }

    public function setIsPaid(bool $isPaid): self {
        $this->isPaid = $isPaid;

        return $this;
    }

    public function getRawStripeSubscription(): ?string
    {
        return $this->rawStripeSubscription;
    }

    public function setRawStripeSubscription(string $rawStripeSubscription): self
    {
        $this->rawStripeSubscription = $rawStripeSubscription;

        return $this;
    }

    public function getSubscriptionChangeRequest(): ?SubscriptionChangeRequest
    {
        return $this->subscriptionChangeRequest;
    }

    public function setSubscriptionChangeRequest(SubscriptionChangeRequest $subscriptionChangeRequest): self
    {
        $this->subscriptionChangeRequest = $subscriptionChangeRequest;

        // set the owning side of the relation if necessary
        if ($this !== $subscriptionChangeRequest->getSubscription()) {
            $subscriptionChangeRequest->setSubscription($this);
        }

        return $this;
    }
}
