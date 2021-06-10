<?php

namespace App\Entity;

use App\Entity\Stripe\StripeCustomer as Customer;
use App\Entity\User\SavedCity;
use App\Entity\User\SavedSearch;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Sonata\UserBundle\Entity\BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * User.
 *
 * @ORM\Table(name="user")
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity(
 *     fields={"email"},
 *     repositoryMethod="findByEmail",
 *     message="It already looks like you have an account."
 * )
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap(
 *     {
 *     "user" = "User",
 *     "city-user" = "App\Entity\User\CityUser",
 *     "job-seeker" = "App\Entity\User\JobSeekerUser",
 *     }
 * )
 */
class User extends BaseUser
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\User\SavedCity", mappedBy="user", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $savedCities;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\User\SavedSearch", mappedBy="user", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $savedSearches;

    /**
     * @var \App\Entity\Stripe\StripeCustomer
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Stripe\StripeCustomer")
     */
    private $stripeCustomer;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $rawStripeCustomer;

    /**
     * @var string
     */
    protected $email;

    public function getCustomerId() {
        if ($this->getStripeCustomer()) {
            return $this->getStripeCustomer()->getStripeId();
        }
        
        if ($this->getRawStripeCustomer()) {
            return $this->getRawStripeCustomer();
        }
        return '';
    }

    public function getSubscriptionId() {
        if (method_exists($this, 'getSubscription')) {
            $subscription = $this->getSubscription();
            if ($subscription) {
                return $subscription->getPaymentProcessorSubscriptionId();
            }
        }
        return '';
    }

    public function __toString()
    {
        return (string) $this->getFirstname().' '.$this->getLastname();
    }

    public function __construct()
    {
        parent::__construct();
        $this->savedCities = new ArrayCollection();
        $this->savedSearches = new ArrayCollection();
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail($email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection|SavedCity[]
     */
    public function getSavedCities(): Collection
    {
        return $this->savedCities;
    }

    public function addSavedCity(SavedCity $savedCity): self
    {
        if (!$this->savedCities->contains($savedCity)) {
            $this->savedCities[] = $savedCity;
            $savedCity->setUser($this);
        }

        return $this;
    }

    public function removeSavedCity(SavedCity $savedCity): self
    {
        if ($this->savedCities->contains($savedCity)) {
            $this->savedCities->removeElement($savedCity);
            // set the owning side to null (unless already changed)
            if ($savedCity->getUser() === $this) {
                $savedCity->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|SavedSearch[]
     */
    public function getSavedSearches(): Collection
    {
        return $this->savedSearches;
    }

    public function addSavedSearch(SavedSearch $savedSearch): self
    {
        if (!$this->savedSearches->contains($savedSearch)) {
            $this->savedSearches[] = $savedSearch;
            $savedSearch->setUser($this);
        }

        return $this;
    }

    public function removeSavedSearch(SavedSearch $savedSearch): self
    {
        if ($this->savedSearches->contains($savedSearch)) {
            $this->savedSearches->removeElement($savedSearch);
            // set the owning side to null (unless already changed)
            if ($savedSearch->getUser() === $this) {
                $savedSearch->setUser(null);
            }
        }

        return $this;
    }

    public function getStripeCustomer(): ?Customer
    {
        return $this->stripeCustomer;
    }

    public function setStripeCustomer(?Customer $stripeCustomer): self
    {
        $this->stripeCustomer = $stripeCustomer;

        return $this;
    }

    public function getRawStripeCustomer(): ?string
    {
        return $this->rawStripeCustomer;
    }

    public function setRawStripeCustomer(string $rawStripeCustomer): self
    {
        $this->rawStripeCustomer = $rawStripeCustomer;

        return $this;
    }
}


