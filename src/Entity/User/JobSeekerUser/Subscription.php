<?php

namespace App\Entity\User\JobSeekerUser;

use App\Entity\SubscriptionPlan\JobSeekerSubscriptionPlan;
use App\Entity\User\JobSeekerUser;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\User\Subscription as BaseSubscription;

/**
 * @ORM\Entity(repositoryClass="App\Repository\User\JobSeekerUser\SubscriptionRepository")
 */
class Subscription extends BaseSubscription
{

    /**
     * @var JobSeekerUser
     *
     * @ORM\OneToOne(targetEntity="App\Entity\User\JobSeekerUser", inversedBy="subscription", cascade={"persist", "remove"})
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=true)
     */
    private $jobSeekerUser;

    /**
     * @var JobSeekerSubscriptionPlan
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\SubscriptionPlan\JobSeekerSubscriptionPlan")
     * @ORM\JoinColumn(nullable=false)
     */
    private $subscriptionPlan;


    public function getJobSeekerUser(): ?JobSeekerUser
    {
        return $this->jobSeekerUser;
    }

    public function setJobSeekerUser(JobSeekerUser $jobSeekerUser): self
    {
        $this->jobSeekerUser = $jobSeekerUser;

        return $this;
    }

    public function getSubscriptionPlan(): ?JobSeekerSubscriptionPlan
    {
        return $this->subscriptionPlan;
    }

    public function setSubscriptionPlan(?JobSeekerSubscriptionPlan $subscriptionPlan): self
    {
        $this->subscriptionPlan = $subscriptionPlan;

        return $this;
    }

}
