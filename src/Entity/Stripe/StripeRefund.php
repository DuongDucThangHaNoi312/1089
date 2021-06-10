<?php

namespace App\Entity\Stripe;

use Doctrine\ORM\Mapping as ORM;
use Miracode\StripeBundle\Model\AbstractRefundModel;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Stripe\StripeRefundRepository")
 */
class StripeRefund extends AbstractRefundModel
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    public function getId(): ?int
    {
        return $this->id;
    }
}
