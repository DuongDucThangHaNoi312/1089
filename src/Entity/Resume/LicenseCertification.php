<?php

namespace App\Entity\Resume;

use App\Entity\User;
use App\Entity\User\JobSeekerUser\Resume;
use Doctrine\ORM\Mapping as ORM;
use App\Traits\BlameableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Resume\LicenseCertificationRepository")
 */
class LicenseCertification
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
     * @Assert\NotBlank(groups={"job_seeker_resume_key_qualifications"})
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User\JobSeekerUser\Resume", inversedBy="licenseCertifications")
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     */
    private $resume;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

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
