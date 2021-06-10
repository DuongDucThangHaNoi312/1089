<?php

namespace App\Entity;

use App\Entity\Lookup\UrlType;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UrlRepository")
 */
class Url
{

    const JOBSEEKER_DEFAULT_TYPE = 3;
    const CITYUSER_DEFAULT_THPE = 1;
    const JOBDESCRIPTION_TYPE = 4;
    const AGREEMENT_TYPE = 5;
    const SALARY_TYPE = 6;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var \App\Entity\Lookup\UrlType
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Lookup\UrlType")
     */
    private $type;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $value;

    /**
     * @var \App\Entity\City
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\City", inversedBy="urls")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $city;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $lastTestedDate;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isUrlTested = false;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $clickCount;

    public function __toString()
    {
        return (string) $this->getType() .':' . $this->getValue();
    }

    public function getTestUrl()
    {
        if($this->getId()) {
            return $this;
        }

        return null;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(string $value): self
    {
        $this->value = $value;

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

    public function getType(): ?UrlType
    {
        return $this->type;
    }

    public function setType(?UrlType $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getIsUrlTested(): ?bool
    {
        return $this->isUrlTested;
    }

    public function setIsUrlTested(bool $isUrlTested): self
    {
        $this->isUrlTested = $isUrlTested;

        return $this;
    }

    public function getLastTestedDate(): ?\DateTimeInterface
    {
        return $this->lastTestedDate;
    }

    public function setLastTestedDate(?\DateTimeInterface $lastTestedDate): self
    {
        $this->lastTestedDate = $lastTestedDate;

        return $this;
    }

    public function getClickCount(): ?int
    {
        return $this->clickCount;
    }

    public function setClickCount(?int $clickCount): self
    {
        $this->clickCount = $clickCount;

        return $this;
    }
}
