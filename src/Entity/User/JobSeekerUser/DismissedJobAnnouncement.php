<?php

namespace App\Entity\User\JobSeekerUser;


use App\Entity\JobAnnouncement;
use App\Entity\User\JobSeekerUser;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\User\JobSeekerUser\DismissedJobAnnouncementRepository")
 * @UniqueEntity(fields={"jobSeekerUser", "jobAnnouncement"}, message="You've already dismissed this job announcement.")
 */
class DismissedJobAnnouncement
{
    use TimestampableEntity;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User\JobSeekerUser", inversedBy="dismissedJobAnnouncements")
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     */
    private $jobSeekerUser;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\JobAnnouncement")
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     */
    private $jobAnnouncement;

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

    public function getJobAnnouncement(): ?JobAnnouncement
    {
        return $this->jobAnnouncement;
    }

    public function setJobAnnouncement(?JobAnnouncement $jobAnnouncement): self
    {
        $this->jobAnnouncement = $jobAnnouncement;

        return $this;
    }
}
