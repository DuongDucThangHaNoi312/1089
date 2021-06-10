<?php

namespace App\Entity\City;

use App\Entity\JobAnnouncement;
use App\Entity\JobTitle\Lookup\JobCategory;
use App\Entity\City;
use App\Entity\JobTitle\Lookup\JobLevel;
use App\Entity\JobTitle\Lookup\JobTitleName;
use App\Entity\JobTitle\Lookup\JobType;
use App\Entity\User\CityUser;
use App\Entity\User\JobSeekerUser\SavedJobTitle;
use App\Entity\User\JobSeekerUser\SubmittedJobTitleInterest;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use App\Traits\BlameableEntity;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use App\Validator\Constraints as CGJ;

/**
 * @ORM\Entity(repositoryClass="App\Repository\City\JobTitleRepository")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false, hardDelete=true)
 * @CGJ\JobTitleConstraint(groups={"job_title_creation", "Default"})
 */
class JobTitle
{
    public function getCountOfSubmittedInterest()
    {
        return count($this->getSubmittedJobTitleInterests());
    }

    use TimestampableEntity;
    use BlameableEntity;
    use SoftDeleteableEntity;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\JobTitle\Lookup\JobTitleName", inversedBy="jobTitles")
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=true)
     */
    private $jobTitleName;

    /**
     * @var \App\Entity\JobTitle\Lookup\JobLevel
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\JobTitle\Lookup\JobLevel")
     * @ORM\OrderBy({"name" = "ASC"})
     * @ORM\JoinColumn(onDelete="SET NULL")
     * @Assert\Expression("this.getId() == null || value != null", message="This value should not be blank.", groups={"job_title_creation"})
     */
    private $level;

    /**
     * @var \App\Entity\JobTitle\Lookup\JobCategory
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\JobTitle\Lookup\JobCategory", inversedBy="jobTitles")
     * @ORM\OrderBy({"name" = "ASC"})
     * @Assert\NotBlank(groups={"job_title_creation"})
     */
    private $category;

    /**
     * @var \App\Entity\JobTitle\Lookup\JobType
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\JobTitle\Lookup\JobType")
     * @ORM\JoinColumn(onDelete="SET NULL")
     * @ORM\OrderBy({"name" = "ASC"})
     * @Assert\NotBlank(groups={"job_title_creation"})
     */
    private $type;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $positionCount;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $titleCount;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    private $monthlySalaryLow;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    private $monthlySalaryHigh;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    private $hourlyWageHigh;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    private $hourlyWageLow;

    /**
     * @var \App\Entity\City\Department
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\City\Department", inversedBy="jobTitles")
     * @ORM\JoinColumn(name="department_id", referencedColumnName="id", onDelete="SET NULL")
     * @Assert\NotBlank(groups={"job_title_creation"})
     */
    private $department;

    /**
     * @var Division
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\City\Division", inversedBy="jobTitles")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $division;

    /**
     * @var \App\Entity\City
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\City", inversedBy="jobTitles")
     * @ORM\JoinColumn(name="city_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $city;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $isVacant = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_hidden", type="boolean", nullable=true)
     */
    private $isHidden = false;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="hidden_on", type="datetime", nullable=true)
     */
    private $hiddenOn;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="\App\Entity\User\JobSeekerUser\SubmittedJobTitleInterest", mappedBy="jobTitle", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $submittedJobTitleInterests;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="\App\Entity\User\JobSeekerUser\SavedJobTitle", mappedBy="jobTitle", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $savedJobTitles;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\JobAnnouncement", mappedBy="jobTitle", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $jobAnnouncements;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isClosedPromotional = false;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User\CityUser")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $markedVacantBy;

    /**
     * aliasing the autogenerated "get" version
     *
     * @return bool|null
     */
    public function isClosedPromotional()
    {
        return $this->getIsClosedPromotional();
    }

    /**
     *
     * string name field removed in favor of ManyToOne to JobTitleName
     * keeping this method for convenience with prior code
     *
     * @return string|null
     */
    public function getName()
    {
        return (string) $this->getJobTitleName();
    }

    public function getSubmittedJobTitleInterestCount()
    {
        return $this->getSubmittedJobTitleInterests()->count();
    }

    public function removeAllJobAnnouncements()
    {
        foreach ($this->getJobAnnouncements() as $ja) {
            $this->removeJobAnnouncement($ja);
        }
        return $this;
    }

    public function removeAllJobTitleInterest()
    {
        foreach ($this->getSubmittedJobTitleInterests() as $si) {
            $this->removeSubmittedJobTitleInterest($si);
        }
        return $this;
    }

    public function allowChangeLevel() {
        return ($this->getLevel() && in_array($this->getLevel()->getSlug(), ['senior', 'executive']) &&
            ($this->getCity()->getSubscription() && $this->getCity()->getSubscription()->getSubscriptionPlan()->getAllowedChangeHideExecutiveSeniorJobLevelPositions() != true));
    }

    public function __toString()
    {
        return (string) $this->getName();
    }

    public function __construct()
    {
        $this->category = new ArrayCollection();
        $this->submittedJobTitleInterests = new ArrayCollection();
        $this->jobAnnouncements = new ArrayCollection();
        $this->savedJobTitles = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPositionCount(): ?int
    {
        return $this->positionCount;
    }

    public function setPositionCount(?int $positionCount): self
    {
        $this->positionCount = $positionCount;

        return $this;
    }

    public function getTitleCount(): ?int
    {
        return $this->titleCount;
    }

    public function setTitleCount(?int $titleCount): self
    {
        $this->titleCount = $titleCount;

        return $this;
    }

    public function getMonthlySalaryLow()
    {
        return $this->monthlySalaryLow;
    }

    public function setMonthlySalaryLow($monthlySalaryLow): self
    {
        $this->monthlySalaryLow = $monthlySalaryLow;

        return $this;
    }

    public function getMonthlySalaryHigh()
    {
        return $this->monthlySalaryHigh;
    }

    public function setMonthlySalaryHigh($monthlySalaryHigh): self
    {
        $this->monthlySalaryHigh = $monthlySalaryHigh;

        return $this;
    }

    public function getHourlyWageHigh()
    {
        return $this->hourlyWageHigh;
    }

    public function setHourlyWageHigh($hourlyWageHigh): self
    {
        $this->hourlyWageHigh = $hourlyWageHigh;

        return $this;
    }

    public function getHourlyWageLow()
    {
        return $this->hourlyWageLow;
    }

    public function setHourlyWageLow($hourlyWageLow): self
    {
        $this->hourlyWageLow = $hourlyWageLow;

        return $this;
    }

    public function getIsVacant(): ?bool
    {
        return $this->isVacant;
    }

    public function setIsVacant(bool $isVacant): self
    {
        $this->isVacant = $isVacant;

        return $this;
    }

    public function getIsHidden(): ?bool
    {
        return $this->isHidden;
    }

    public function setIsHidden(?bool $isHidden): self
    {
        $this->isHidden = $isHidden;

        return $this;
    }

    public function getHiddenOn(): ?\DateTimeInterface
    {
        return $this->hiddenOn;
    }

    public function setHiddenOn(?\DateTimeInterface $hiddenOn): self
    {
        $this->hiddenOn = $hiddenOn;

        return $this;
    }

    public function getLevel(): ?JobLevel
    {
        return $this->level;
    }

    public function setLevel(?JobLevel $level): self
    {
        $this->level = $level;

        return $this;
    }

    /**
     * @return Collection|JobCategory[]
     */
    public function getCategory(): Collection
    {
        return $this->category;
    }

    public function addCategory(JobCategory $category): self
    {
        if (!$this->category->contains($category)) {
            $this->category[] = $category;
        }

        return $this;
    }

    public function removeCategory(JobCategory $category): self
    {
        if ($this->category->contains($category)) {
            $this->category->removeElement($category);
        }

        return $this;
    }

    public function getType(): ?JobType
    {
        return $this->type;
    }

    public function setType(?JobType $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getDepartment(): ?Department
    {
        return $this->department;
    }

    public function setDepartment(?Department $department): self
    {
        $this->department = $department;

        return $this;
    }

    public function getDivision(): ?Division
    {
        return $this->division;
    }

    public function setDivision(?Division $division): self
    {
        $this->division = $division;

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

    /**
     * @return Collection|SubmittedJobTitleInterest[]
     */
    public function getSubmittedJobTitleInterests(): Collection
    {
        return $this->submittedJobTitleInterests;
    }

    public function addSubmittedJobTitleInterest(SubmittedJobTitleInterest $submittedJobTitleInterest): self
    {
        if (!$this->submittedJobTitleInterests->contains($submittedJobTitleInterest)) {
            $this->submittedJobTitleInterests[] = $submittedJobTitleInterest;
            $submittedJobTitleInterest->setJobTitle($this);
        }

        return $this;
    }

    public function removeSubmittedJobTitleInterest(SubmittedJobTitleInterest $submittedJobTitleInterest): self
    {
        if ($this->submittedJobTitleInterests->contains($submittedJobTitleInterest)) {
            $this->submittedJobTitleInterests->removeElement($submittedJobTitleInterest);
            // set the owning side to null (unless already changed)
            if ($submittedJobTitleInterest->getJobTitle() === $this) {
                $submittedJobTitleInterest->setJobTitle(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|JobAnnouncement[]
     */
    public function getJobAnnouncements(): Collection
    {
        return $this->jobAnnouncements;
    }

    public function addJobAnnouncement(JobAnnouncement $jobAnnouncement): self
    {
        if (!$this->jobAnnouncements->contains($jobAnnouncement)) {
            $this->jobAnnouncements[] = $jobAnnouncement;
            $jobAnnouncement->setJobTitle($this);
        }

        return $this;
    }

    public function removeJobAnnouncement(JobAnnouncement $jobAnnouncement): self
    {
        if ($this->jobAnnouncements->contains($jobAnnouncement)) {
            $this->jobAnnouncements->removeElement($jobAnnouncement);
            // set the owning side to null (unless already changed)
            if ($jobAnnouncement->getJobTitle() === $this) {
                $jobAnnouncement->setJobTitle(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|SavedJobTitle[]
     */
    public function getSavedJobTitles(): Collection
    {
        return $this->savedJobTitles;
    }

    public function addSavedJobTitle(SavedJobTitle $savedJobTitle): self
    {
        if (!$this->savedJobTitles->contains($savedJobTitle)) {
            $this->savedJobTitles[] = $savedJobTitle;
            $savedJobTitle->setJobTitle($this);
        }

        return $this;
    }

    public function removeSavedJobTitle(SavedJobTitle $savedJobTitle): self
    {
        if ($this->savedJobTitles->contains($savedJobTitle)) {
            $this->savedJobTitles->removeElement($savedJobTitle);
            // set the owning side to null (unless already changed)
            if ($savedJobTitle->getJobTitle() === $this) {
                $savedJobTitle->setJobTitle(null);
            }
        }

        return $this;
    }

    public function getJobTitleName(): ?JobTitleName
    {
        return $this->jobTitleName;
    }

    public function setJobTitleName(?JobTitleName $jobTitleName): self
    {
        $this->jobTitleName = $jobTitleName;

        return $this;
    }

    public function getIsClosedPromotional(): ?bool
    {
        return $this->isClosedPromotional;
    }

    public function setIsClosedPromotional(?bool $isClosedPromotional): self
    {
        $this->isClosedPromotional = $isClosedPromotional;

        return $this;
    }

    public function getMarkedVacantBy(): ?CityUser
    {
        return $this->markedVacantBy;
    }

    public function setMarkedVacantBy(?CityUser $markedVacantBy): self
    {
        $this->markedVacantBy = $markedVacantBy;

        return $this;
    }

}
