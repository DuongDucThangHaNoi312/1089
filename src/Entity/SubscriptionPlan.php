<?php

namespace App\Entity;

use App\Entity\Stripe\StripePlan as Plan;
use App\Entity\SubscriptionPlan\Lookup\RenewalFrequency;
use App\Entity\SubscriptionPlan\PriceSchedule;
use App\Entity\User\SubscriptionChangeRequest;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Traits\BlameableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SubscriptionPlanRepository")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap(
 *     {
 *     "job-seeker" = "App\Entity\SubscriptionPlan\JobSeekerSubscriptionPlan",
 *     "city" = "App\Entity\SubscriptionPlan\CitySubscriptionPlan",
 *     "subscription-plan" = "SubscriptionPlan",
 *     }
 * )
 */
class SubscriptionPlan
{
    use BlameableEntity;
    use TimestampableEntity;

    const CITY_TRIAL_PLAN_ID = 1; // granted automatically upon initial registration
    const JOB_SEEKER_TRIAL_PLAN_ID = 6; // granted automatically upon initial registration

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Gedmo\Slug(handlers={
     *      @Gedmo\SlugHandler(class="Gedmo\Sluggable\Handler\RelativeSlugHandler", options={
     *          @Gedmo\SlugHandlerOption(name="relationField", value="renewalFrequency"),
     *          @Gedmo\SlugHandlerOption(name="relationSlugField", value="slug"),
     *          @Gedmo\SlugHandlerOption(name="separator", value="-")
     *      })
     * }, separator="-", updatable=false, fields={"name"})
     * @ORM\Column(type="string", length=255)
     */
    private $slug;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="decimal", precision=5, scale=2)
     */
    private $price;

    /**
     * @ORM\Column(type="decimal", precision=5, scale=2, nullable=true)
     */
    private $nextPrice;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\SubscriptionPlan\Lookup\RenewalFrequency")
     * @ORM\JoinColumn(nullable=false)
     */
    private $renewalFrequency;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="date", nullable=true)
     */
    private $nextPriceEffectiveDate;

    /**
     * @var \App\Entity\Stripe\StripePlan
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Stripe\StripePlan")
     */
    private $stripePlan;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $rawStripePlan;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    private $isActive = false;

    public function priceToFloat() {
        return floatval($this->getPrice());
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getRenewalFrequency(): ?RenewalFrequency
    {
        return $this->renewalFrequency;
    }

    public function setRenewalFrequency(?RenewalFrequency $renewalFrequency): self
    {
        $this->renewalFrequency = $renewalFrequency;

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

    public function getStripePlan(): ?Plan
    {
        return $this->stripePlan;
    }

    public function setStripePlan(?Plan $stripePlan): self
    {
        $this->stripePlan = $stripePlan;

        return $this;
    }

    public function getIsActive(): bool {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self {
        $this->isActive = $isActive;

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
