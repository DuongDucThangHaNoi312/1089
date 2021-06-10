<?php

namespace App\Entity;

use App\Entity\User\JobSeekerUser;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AlertedJobAnnouncementRepository")
 */
class AlertedJobAnnouncement
{
    use TimestampableEntity;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\JobAnnouncement", inversedBy="alertedJobAnnouncements")
     * @ORM\JoinColumn(nullable=false)
     */
    private $jobAnnouncement;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User\JobSeekerUser", inversedBy="alertedJobAnnouncements")
     * @ORM\JoinColumn(nullable=false)
     */
    private $jobSeeker;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getJobSeeker(): ?JobSeekerUser
    {
        return $this->jobSeeker;
    }

    public function setJobSeeker(?JobSeekerUser $jobSeeker): self
    {
        $this->jobSeeker = $jobSeeker;

        return $this;
    }
}
