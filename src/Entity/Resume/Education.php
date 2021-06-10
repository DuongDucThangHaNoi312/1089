<?php

namespace App\Entity\Resume;

use App\Entity\Resume\Lookup\DegreeType;
use App\Entity\User;
use App\Entity\User\JobSeekerUser\Resume;
use Doctrine\ORM\Mapping as ORM;
use App\Traits\BlameableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Resume\EducationRepository")
 */
class Education
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
    private $major;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Resume\Lookup\DegreeType")
     * @ORM\JoinColumn(onDelete="SET NULL")
     * @Assert\NotBlank(groups={"job_seeker_resume_key_qualifications"})
     */
    private $degreeType;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User\JobSeekerUser\Resume", inversedBy="education", cascade={"persist"})
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     */
    private $resume;

    public function __toString()
    {
        return (string) $this->getDegreeType(). ", " . (string) $this->getMajor();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMajor(): ?string
    {
        return $this->major;
    }

    public function setMajor(string $major): self
    {
        $this->major = $major;

        return $this;
    }

    public function getDegreeType(): ?DegreeType
    {
        return $this->degreeType;
    }

    public function setDegreeType(?DegreeType $degreeType): self
    {
        $this->degreeType = $degreeType;

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
