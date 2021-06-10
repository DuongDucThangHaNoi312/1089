<?php

namespace App\Entity\Stripe;

use Doctrine\ORM\Mapping as ORM;
use Miracode\StripeBundle\Model\AbstractCustomerModel;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Stripe\StripeCustomerRepository")
 */
class StripeCustomer extends AbstractCustomerModel
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
