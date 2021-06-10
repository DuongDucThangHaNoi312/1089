<?php

namespace App\Entity\Resume;

use App\Entity\User;
use App\Entity\User\JobSeekerUser\Resume;
use Doctrine\ORM\Mapping as ORM;
use App\Traits\BlameableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Resume\WorkHistoryRepository")
 */
class WorkHistory
{

    use BlameableEntity;
    use TimestampableEntity;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(groups={"job_seeker_resume_work_history"})
     */
    private $jobTitle;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(groups={"job_seeker_resume_work_history"})
     */
    private $employerName;

    /**
     * @var float
     * @Assert\NotBlank(groups={"job_seeker_resume_work_history"})
     */
    private $yearsOfEmployment;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User\JobSeekerUser\Resume", inversedBy="workHistories")
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     */
    private $resume;

    /**
     * @ORM\Column(type="date")
     * @Assert\NotBlank(groups={"job_seeker_resume_work_history"})
     */
    private $startDate;

    /**
     * @ORM\Column(type="date")
     * @Assert\Expression("value >= this.getStartDate()", message="End date must be greater than or equal to the Start Date", groups={"job_seeker_resume_work_history"})
     */
    private $endDate;

    /* Custom */

    public function getYearsOfEmployment()
    {
        $startDate = $this->startDate ? $this->startDate : new \DateTime();
        $endDate = $this->endDate ? $this->endDate : new \DateTime();
        $difference = $endDate->diff($startDate);

        return $difference->y;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getJobTitle(): ?string
    {
        return $this->jobTitle;
    }

    public function setJobTitle(string $jobTitle): self
    {
        $this->jobTitle = $jobTitle;

        return $this;
    }

    public function getEmployerName(): ?string
    {
        return $this->employerName;
    }

    public function setEmployerName(string $employerName): self
    {
        $this->employerName = $employerName;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;

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

    /* Auto-generated */

}
