<?php

namespace App\Entity\Stripe;

use App\Entity\SubscriptionInterface;
use Doctrine\ORM\Mapping as ORM;
use Miracode\StripeBundle\Model\AbstractSubscriptionModel;

/**
 * @ORM\Table(name="stripe_subscription")
 * @ORM\Entity(repositoryClass="App\Repository\Stripe\StripeSubscriptionRepository")
 */
class StripeSubscription extends AbstractSubscriptionModel implements SubscriptionInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    public function getPaymentProcessorId()
    {
        return $this->getStripeId();
    }

    public function getId(): ?int
    {
        return $this->id;
    }
}
