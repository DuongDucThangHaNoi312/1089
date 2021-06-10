<?php

namespace App\Entity\City;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\City;

/**
 * @ORM\Entity(repositoryClass="App\Repository\City\OperationHoursRepository")
 */
class OperationHours
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $day;

    /**
     * @ORM\Column(type="time")
     */
    private $open;

    /**
     * @ORM\Column(type="time")
     */
    private $close;

    /**
     * @var \App\Entity\City
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\City", inversedBy="operationHours")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $city;

    public function __toString()
    {
        return (string) $this->getDay();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getDay(): ?string
    {
        return $this->day;
    }

    public function setDay(string $day): self
    {
        $this->day = $day;

        return $this;
    }

    public function getOpen(): ?\DateTimeInterface
    {
        return $this->open;
    }

    public function setOpen(\DateTimeInterface $open): self
    {
        $this->open = $open;

        return $this;
    }

    public function getClose(): ?\DateTimeInterface
    {
        return $this->close;
    }

    public function setClose(\DateTimeInterface $close): self
    {
        $this->close = $close;

        return $this;
    }

    public function getCity(): ?City
    {
        return $this->city;
    }

    public function setCity(?City $city): self
    {
        $this->city = $city;

        return $this;
    }
}
