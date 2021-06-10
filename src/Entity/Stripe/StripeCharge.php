<?php

namespace App\Entity\Stripe;

use App\Entity\ChargeInterface;
use Doctrine\ORM\Mapping as ORM;
use Miracode\StripeBundle\Model\AbstractChargeModel;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Stripe\StripeChargeRepository")
 */
class StripeCharge extends AbstractChargeModel implements ChargeInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    public function hasFailed()
    {
        return $this->getStatus() == "failed";
    }

    public function getId(): ?int
    {
        return $this->id;
    }
}
