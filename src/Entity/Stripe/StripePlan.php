<?php

namespace App\Entity\Stripe;

use Doctrine\ORM\Mapping as ORM;
use Miracode\StripeBundle\Model\AbstractPlanModel;
use Miracode\StripeBundle\Annotation\StripeObjectParam;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Stripe\StripePlanRepository")
 */
class StripePlan extends AbstractPlanModel
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @StripeObjectParam(name="nickname")
     *
     * @var string
     */
    protected $name;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName($name): self
    {
        $this->name = $name;

        return $this;
    }

}
