<?php

namespace App\Entity\User\JobSeekerUser;

use App\Entity\City\JobTitle;
use App\Entity\User\JobSeekerUser;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\User\JobSeekerUser\SavedJobTitleRepository")
 * @UniqueEntity(fields={"jobSeekerUser", "jobTitle"}, message="You've already saved this job title.")
 */
class SavedJobTitle
{
    use TimestampableEntity;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var JobSeekerUser
     * @ORM\ManyToOne(targetEntity="App\Entity\User\JobSeekerUser", inversedBy="savedJobTitles")
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     */
    private $jobSeekerUser;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\City\JobTitle", inversedBy="savedJobTitles")
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     */
    private $jobTitle;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getJobSeekerUser(): ?JobSeekerUser
    {
        return $this->jobSeekerUser;
    }

    public function setJobSeekerUser(?JobSeekerUser $jobSeekerUser): self
    {
        $this->jobSeekerUser = $jobSeekerUser;

        return $this;
    }

    public function getJobTitle(): ?JobTitle
    {
        return $this->jobTitle;
    }

    public function setJobTitle(?JobTitle $jobTitle): self
    {
        $this->jobTitle = $jobTitle;

        return $this;
    }

    
}
