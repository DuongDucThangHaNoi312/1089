<?php

namespace App\Entity\User\CityUser;

use App\Entity\User\JobSeekerUser\Resume;
use App\Entity\User\CityUser;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\User\CityUser\SavedResumeRepository")
 * @UniqueEntity(fields={"cityUser", "resume"}, message="You've already saved this resume.")
 */
class SavedResume
{
    use TimestampableEntity;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User\CityUser", inversedBy="savedResumes")
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     */
    private $cityUser;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User\JobSeekerUser\Resume")
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     */
    private $resume;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCityUser(): ?CityUser
    {
        return $this->cityUser;
    }

    public function setCityUser(?CityUser $cityUser): self
    {
        $this->cityUser = $cityUser;

        return $this;
    }

    public function getResume(): ?Resume
    {
        return $this->resume;
    }

    public function setResume(?Resume $resume): self
    {
        $this->resume = $resume;

        return $this;
    }

}
