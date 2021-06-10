<?php

namespace App\Entity\Stripe;

use App\Entity\CardInterface;
use Doctrine\ORM\Mapping as ORM;
use Miracode\StripeBundle\Model\AbstractCardModel;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Stripe\StripeCardRepository")
 */
class StripeCard extends AbstractCardModel implements CardInterface
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
