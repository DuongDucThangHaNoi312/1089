<?php

namespace App\Entity\JobAnnouncement;

use App\Entity\JobAnnouncement;
use App\Entity\User\JobSeekerUser;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\JobAnnouncement\ViewRepository")
 * @ORM\Table(name="job_announcement_view")
 */
class View
{

    use TimestampableEntity;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\JobAnnouncement", inversedBy="views")
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     */
    private $jobAnnouncement;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User\JobSeekerUser")
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=true)
     */
    private $jobSeekerUser;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $ipAddress;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $userAgent;

    public function __construct(JobAnnouncement $jobAnnouncement, $jobSeekerUser = null)
    {
        $this->setJobAnnouncement($jobAnnouncement);
        if ($jobAnnouncement instanceof JobAnnouncement && $jobSeekerUser && $jobSeekerUser instanceof JobSeekerUser) {
            $this->setJobSeekerUser($jobSeekerUser);
        } else {
            $this->setJobSeekerUser(null);
        }
    }

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

    public function getJobSeekerUser(): ?JobSeekerUser
    {
        return $this->jobSeekerUser;
    }

    public function setJobSeekerUser(?JobSeekerUser $jobSeekerUser): self
    {
        $this->jobSeekerUser = $jobSeekerUser;

        return $this;
    }

    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function setIpAddress(?string $ipAddress): self
    {
        $this->ipAddress = $ipAddress;

        return $this;
    }

    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }

    public function setUserAgent(?string $userAgent): self
    {
        $this->userAgent = $userAgent;

        return $this;
    }

}
