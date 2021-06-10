<?php

namespace App\Entity\User\JobSeekerUser;

use App\Entity\City\JobTitle;
use App\Entity\User\JobSeekerUser;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\User\JobSeekerUser\SubmittedJobTitleInterestRepository")
 * @UniqueEntity(fields={"jobSeekerUser", "jobTitle"}, message="You've already submitted interest in this job title.")
 */
class SubmittedJobTitleInterest
{
    use TimestampableEntity;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var JobTitle
     * @ORM\ManyToOne(targetEntity="\App\Entity\City\JobTitle", inversedBy="submittedJobTitleInterests")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $jobTitle;

    /**
     * @var JobSeekerUser
     * @ORM\ManyToOne(targetEntity="\App\Entity\User\JobSeekerUser", inversedBy="submittedJobTitleInterests")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $jobSeekerUser;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getJobSeekerUser(): ?JobSeekerUser
    {
        return $this->jobSeekerUser;
    }

    public function setJobSeekerUser(?JobSeekerUser $jobSeekerUser): self
    {
        $this->jobSeekerUser = $jobSeekerUser;

        return $this;
    }


}
