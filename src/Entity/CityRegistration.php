<?php

namespace App\Entity;

use App\Entity\CityRegistration\Lookup\CityRegistrationStatus;
use App\Entity\User\CityUser;
use Doctrine\ORM\Mapping as ORM;
use App\Traits\BlameableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CityRegistrationRepository")
 */
class CityRegistration
{

    const STATUS_PENDING = 1;
    const STATUS_APPROVED = 2;
    const STATUS_REJECTED = 3;

    use BlameableEntity;
    use TimestampableEntity;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var CityUser
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\User\CityUser", inversedBy="cityRegistrations")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $cityUser;

    /**
     * @var City
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\City", inversedBy="cityRegistrations")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $city;

    /**
     * @var CityRegistrationStatus
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\CityRegistration\Lookup\CityRegistrationStatus")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $status;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\NotBlank(groups={"registration_step_two"})
     */
    private $department;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\NotBlank(groups={"registration_step_two"})
     */
    private $jobTitle;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\NotBlank(groups={"registration_passcode"})
     */
    private $passcode;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\NotBlank(groups={"registration_verification"})
     */
    private $cityHallAddress;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\NotBlank(groups={"registration_verification"})
     */
    private $cityHallZip;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\NotBlank(groups={"registration_verification"})
     */
    private $cityHallMainPhone;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\NotBlank(groups={"registration_verification"})
     */
    private $cityWebsite;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\NotBlank(groups={"registration_verification"})
     */
    private $cityTimezone;

    /**
     * @ORM\Column(name="city_information_match", type="boolean", nullable=true)
     * @Assert\NotNull()
     */
    private $cityInformationMatch;

    /**
     * @ORM\Column(name="applicant_work_for_city", type="boolean", nullable=true)
     * @Assert\NotNull()
     */
    private $applicantWorkForCity;

    /**
     * @ORM\Column(name="applied_to_use_system", type="boolean", nullable=true)
     * @Assert\NotNull()
     */
    private $appliedToUseSystem;

    /**
     * @ORM\Column(name="work_for_department_with_job_title", type="boolean", nullable=true)
     * @Assert\NotNull()
     */
    private $workForDepartmentWithJobTitle;

    /**
     * @ORM\Column(name="responsible_to_advertise_job_openings_for_city", type="boolean", nullable=true)
     * @Assert\NotNull()
     */
    private $responsibleToAdvertiseJobOpeningsForCity;

    /**
     * @ORM\Column(name="telephone_and_email_match", type="boolean", nullable=true)
     * @Assert\NotNull()
     */
    private $telephoneAndEmailMatch;

    /**
     * @ORM\Column(name="explanation", type="text", nullable=true)
     */
    private $explanation;

    /**
     * @var boolean
     *
     * @ORM\Column(name="decision_sent", type="boolean")
     */
    private $decisionSent = false;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="decision_date", type="datetime", nullable=true)
     */
    private $decisionDate;


    public function __toString()
    {
        return $this->getCity() . ' Registration by ' . $this->getCityUser()->getFirstname().' '.$this->getCityUser()->getLastname();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDepartment(): ?string
    {
        return $this->department;
    }

    public function setDepartment(?string $department): self
    {
        $this->department = $department;

        return $this;
    }

    public function getJobTitle(): ?string
    {
        return $this->jobTitle;
    }

    public function setJobTitle(?string $jobTitle): self
    {
        $this->jobTitle = $jobTitle;

        return $this;
    }

    public function getPasscode(): ?string
    {
        return $this->passcode;
    }

    public function setPasscode(?string $passcode): self
    {
        $this->passcode = $passcode;

        return $this;
    }

    public function getCityHallAddress(): ?string
    {
        return $this->cityHallAddress;
    }

    public function setCityHallAddress(?string $cityHallAddress): self
    {
        $this->cityHallAddress = $cityHallAddress;

        return $this;
    }

    public function getCityHallZip(): ?string
    {
        return $this->cityHallZip;
    }

    public function setCityHallZip(?string $cityHallZip): self
    {
        $this->cityHallZip = $cityHallZip;

        return $this;
    }

    public function getCityHallMainPhone(): ?string
    {
        return $this->cityHallMainPhone;
    }

    public function setCityHallMainPhone(?string $cityHallMainPhone): self
    {
        $this->cityHallMainPhone = $cityHallMainPhone;

        return $this;
    }

    public function getCityWebsite(): ?string
    {
        return $this->cityWebsite;
    }

    public function setCityWebsite(?string $cityWebsite): self
    {
        $this->cityWebsite = $cityWebsite;

        return $this;
    }

    public function getCityTimezone(): ?string
    {
        return $this->cityTimezone;
    }

    public function setCityTimezone(?string $cityTimezone): self
    {
        $this->cityTimezone = $cityTimezone;

        return $this;
    }

    public function getCityInformationMatch(): ?bool
    {
        return $this->cityInformationMatch;
    }

    public function setCityInformationMatch(?bool $cityInformationMatch): self
    {
        $this->cityInformationMatch = $cityInformationMatch;

        return $this;
    }

    public function getApplicantWorkForCity(): ?bool
    {
        return $this->applicantWorkForCity;
    }

    public function setApplicantWorkForCity(?bool $applicantWorkForCity): self
    {
        $this->applicantWorkForCity = $applicantWorkForCity;

        return $this;
    }

    public function getAppliedToUseSystem(): ?bool
    {
        return $this->appliedToUseSystem;
    }

    public function setAppliedToUseSystem(?bool $appliedToUseSystem): self
    {
        $this->appliedToUseSystem = $appliedToUseSystem;

        return $this;
    }

    public function getWorkForDepartmentWithJobTitle(): ?bool
    {
        return $this->workForDepartmentWithJobTitle;
    }

    public function setWorkForDepartmentWithJobTitle(?bool $workForDepartmentWithJobTitle): self
    {
        $this->workForDepartmentWithJobTitle = $workForDepartmentWithJobTitle;

        return $this;
    }

    public function getResponsibleToAdvertiseJobOpeningsForCity(): ?bool
    {
        return $this->responsibleToAdvertiseJobOpeningsForCity;
    }

    public function setResponsibleToAdvertiseJobOpeningsForCity(?bool $responsibleToAdvertiseJobOpeningsForCity): self
    {
        $this->responsibleToAdvertiseJobOpeningsForCity = $responsibleToAdvertiseJobOpeningsForCity;

        return $this;
    }

    public function getTelephoneAndEmailMatch(): ?bool
    {
        return $this->telephoneAndEmailMatch;
    }

    public function setTelephoneAndEmailMatch(?bool $telephoneAndEmailMatch): self
    {
        $this->telephoneAndEmailMatch = $telephoneAndEmailMatch;

        return $this;
    }

    public function getExplanation(): ?string
    {
        return $this->explanation;
    }

    public function setExplanation(?string $explanation): self
    {
        $this->explanation = $explanation;

        return $this;
    }

    public function getDecisionSent(): ?bool
    {
        return $this->decisionSent;
    }

    public function setDecisionSent(bool $decisionSent): self
    {
        $this->decisionSent = $decisionSent;

        return $this;
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

    public function getCity(): ?City
    {
        return $this->city;
    }

    public function setCity(?City $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getStatus(): ?CityRegistrationStatus
    {
        return $this->status;
    }

    public function setStatus(?CityRegistrationStatus $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getDecisionDate(): ?\DateTimeInterface
    {
        return $this->decisionDate;
    }

    public function setDecisionDate(?\DateTimeInterface $decisionDate): self
    {
        $this->decisionDate = $decisionDate;

        return $this;
    }

}