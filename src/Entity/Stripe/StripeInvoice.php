<?php

namespace App\Entity\Stripe;

use App\Entity\InvoiceInterface;
use Doctrine\ORM\Mapping as ORM;
use Miracode\StripeBundle\Model\AbstractInvoiceModel;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Stripe\StripeInvoiceRepository")
 */
class StripeInvoice extends AbstractInvoiceModel implements InvoiceInterface
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
