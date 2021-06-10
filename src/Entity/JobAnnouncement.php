<?php

namespace App\Entity;

use App\Entity\City\JobTitle;
use App\Entity\City\State;
use App\Entity\JobAnnouncement\Lookup\JobAnnouncementStatus;
use App\Entity\JobAnnouncement\JobAnnouncementImpression;
use App\Entity\JobAnnouncement\Lookup\WageSalaryUnit;
use App\Entity\JobAnnouncement\View;
use App\Entity\User\CityUser;
use App\Entity\User\JobSeekerUser\SavedJobAnnouncement;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Traits\BlameableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\Constraints as JobAnnouncementAssert;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @JobAnnouncementAssert\JobAnnouncementConstraint(groups={"job_announcement_details", "job_announcement_active_dates", "job_announcement_application_deadline", "job_announcement_wage_salary"})
 * @JobAnnouncementAssert\JobAnnouncementDetailsConstraint(groups={"job_announcement_details"})
 * @JobAnnouncementAssert\JobAnnouncementScheduledDatesConstraint(groups={"job_announcement_active_dates"})
 * @JobAnnouncementAssert\JobAnnouncementApplicationDeadlineConstraint(groups={"job_announcement_application_deadline"})
 * @JobAnnouncementAssert\WageSalaryConstraint(groups={"job_announcement_wage_salary"})
 * @ORM\Entity(repositoryClass="App\Repository\JobAnnouncementRepository")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false, hardDelete=true)
 */
class JobAnnouncement
{
    use BlameableEntity;
    use TimestampableEntity;
    use SoftDeleteableEntity;

    const STATUS_TODO = 1;
    const STATUS_DRAFT = 2;
    const STATUS_SCHEDULED = 3;
    const STATUS_ACTIVE = 4;
    const STATUS_ENDED = 5;
    const STATUS_ARCHIVED = 6;
    const CONTINUOUS = 'Continuous';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var JobTitle $jobTitle
     * @ORM\ManyToOne(targetEntity="App\Entity\City\JobTitle", inversedBy="jobAnnouncements", cascade={"persist"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @Assert\NotBlank()
     */
    private $jobTitle;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    private $wageSalaryLow;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    private $wageSalaryHigh;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\JobAnnouncement\Lookup\WageSalaryUnit")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $wageSalaryUnit;

    /**
     * @ORM\Column(type="boolean")
     */
    private $wageRangeDependsOnQualifications = false;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $applicationDeadline;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $applicationUrl;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     * @Assert\NotBlank(groups={"job_announcement_active_dates"})
     */
    private $startsOn;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $endsOn;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $attachedDocument;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\JobAnnouncement\Lookup\JobAnnouncementStatus")
     * @ORM\JoinColumn(onDelete="SET NULL")
     * @Assert\Expression("value != null || this.getId() == null", message="This value should not be blank.")
     */
    private $status;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User\CityUser", inversedBy="jobAnnouncements")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $assignedTo;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    private $isAlert = true;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\JobAnnouncement\View", mappedBy="jobAnnouncement", orphanRemoval=true)
     */
    private $views;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $street;

    /**
     * @var \App\Entity\City
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\City")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $city;

    /**
     * @var \App\Entity\City\State
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\City\State")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $state;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $zipcode;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\AlertedJobAnnouncement", mappedBy="jobAnnouncement", orphanRemoval=true)
     */
    private $alertedJobAnnouncements;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isPostedByCGJ = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $hasNoEndDate = false;

    /**
     * @var \App\Entity\City
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\City")
     */
    private $jobTitleCity;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="\App\Entity\User\JobSeekerUser\SavedJobAnnouncement", mappedBy="jobAnnouncement", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $savedJobAnnouncements;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastTestedDate;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $endDateDescription = self::CONTINUOUS;

    private  $jobAnnouncementURL;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\JobAnnouncement\JobAnnouncementImpression", mappedBy="jobAnnouncement", orphanRemoval=true)
     */
    private $jobAnnouncementImpressions;

    public function checkIsComplete() {
        if ($this->getWageRangeDependsOnQualifications() != true) {
            if ($this->getWageSalaryLow() == null || $this->getWageSalaryHigh() == null || $this->getWageSalaryUnit() == null) {
                return false;
            }
        }

        if ($this->getApplicationDeadline() == null && $this->getHasNoEndDate() == false) {
            return false;
        }

        /* Alert or Announcement */

        if ($this->getIsAlert()) {
            if ($this->getApplicationUrl() == null) {
                return false;
            }
        } else {
            if ($this->getApplicationUrl() == null || $this->getDescription() == null) {
                return false;
            }
        }


        if ($this->getJobTitle()->isClosedPromotional() === null) {
            return false;
        }

        return true;

    }

    public function isSectionComplete(?string $section) :?bool {
        if(!$section) {
            return null;
        }

        $isComplete = true;
        switch($section) {
            case "wage-salary":
                if ($this->getWageRangeDependsOnQualifications() != true) {
                    if ($this->getWageSalaryLow() == null || $this->getWageSalaryHigh() == null || $this->getWageSalaryUnit() == null) {
                        $isComplete = false;
                    }
                }
                break;
            case "application-deadline":
                if ($this->getApplicationDeadline() == null && $this->getHasNoEndDate() == false) {
                    $isComplete = false;
                }
                break;
            case "announcement":
                if ($this->getApplicationUrl() == null || $this->getDescription() == null) {
                    $isComplete = false;
                }
                break;
            case "alert":
                if ($this->getApplicationUrl() == null) {
                    $isComplete = false;
                }
                break;
            case "closed-promotional":
                if ($this->getJobTitle()->isClosedPromotional() === null) {
                    $isComplete = false;
                }
                break;
            default:
                break;
        }

        return $isComplete;
    }

    public function setWageSalaryRange() {
        if ($this->getWageRangeDependsOnQualifications() == true) {
            $this->setWageSalaryHigh(null);
            $this->setWageSalaryLow(null);
            $this->setWageSalaryUnit(null);
        }
    }

    public function __construct()
    {
        $this->views = new ArrayCollection();
        $this->alertedJobAnnouncements = new ArrayCollection();
        $this->savedJobAnnouncements = new ArrayCollection();
        $this->jobAnnouncementImpressions = new ArrayCollection();
    }

    public function __toString()
    {
        return (string)$this->getJobTitle() . " - " . (string)$this->getStatus();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection|SavedJobAnnouncement[]
     */
    public function getSavedJobAnnouncements(): Collection
    {
        return $this->savedJobAnnouncements;
    }

    public function addSavedJobAnnouncement(SavedJobAnnouncement $savedJobAnnouncement): self
    {
        if (!$this->savedJobAnnoucements->contains($savedJobAnnouncement)) {
            $this->savedJobAnnoucements[] = $savedJobAnnouncement;
            $savedJobAnnouncement->setJobAnnouncement($this);
        }

        return $this;
    }

    public function removeSavedJobAnnouncement(SavedJobAnnouncement $savedJobAnnouncement): self
    {
        if ($this->savedJobAnnoucements->contains($savedJobAnnouncement)) {
            $this->savedJobAnnoucements->removeElement($savedJobAnnouncement);
            // set the owning side to null (unless already changed)
            if ($savedJobAnnouncement->getJobTitle() === $this) {
                $savedJobAnnouncement->setJobTitle(null);
            }
        }

        return $this;
    }

    public function getWageSalaryLow()
    {
        return $this->wageSalaryLow;
    }

    public function setWageSalaryLow($wageSalaryLow): self
    {
        $this->wageSalaryLow = $wageSalaryLow;

        return $this;
    }

    public function getWageSalaryHigh()
    {
        return $this->wageSalaryHigh;
    }

    public function setWageSalaryHigh($wageSalaryHigh): self
    {
        $this->wageSalaryHigh = $wageSalaryHigh;

        return $this;
    }

    public function getApplicationDeadline(): ?\DateTime
    {
        return $this->applicationDeadline;
    }

    public function setApplicationDeadline(?\DateTime $applicationDeadline): self
    {
        $this->applicationDeadline = $applicationDeadline;

        return $this;
    }

    public function getApplicationUrl(): ?string
    {
        return $this->applicationUrl;
    }

    public function setApplicationUrl(?string $applicationUrl): self
    {
        $this->applicationUrl = $applicationUrl;

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

    public function getStartsOn(): ?\DateTime
    {
        return $this->startsOn;
    }

    public function setStartsOn(?\DateTime $startsOn): self
    {
        $this->startsOn = $startsOn;

        return $this;
    }

    public function getEndsOn(): ?\DateTime
    {
        return $this->endsOn;
    }

    public function setEndsOn(?\DateTime $endsOn): self
    {
        $this->endsOn = $endsOn;

        return $this;
    }

    public function getAttachedDocument(): ?int
    {
        return $this->attachedDocument;
    }

    public function setAttachedDocument(?int $attachedDocument): self
    {
        $this->attachedDocument = $attachedDocument;

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

    public function getWageSalaryUnit(): ?WageSalaryUnit
    {
        return $this->wageSalaryUnit;
    }

    public function setWageSalaryUnit(?WageSalaryUnit $wageSalaryUnit): self
    {
        $this->wageSalaryUnit = $wageSalaryUnit;

        return $this;
    }

    public function getStatus(): ?JobAnnouncementStatus
    {
        return $this->status;
    }

    public function setStatus(?JobAnnouncementStatus $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getAssignedTo(): ?CityUser
    {
        return $this->assignedTo;
    }

    public function setAssignedTo(?CityUser $assignedTo): self
    {
        $this->assignedTo = $assignedTo;

        return $this;
    }

    /**
     * @return Collection|View[]
     */
    public function getViews(): Collection
    {
        return $this->views;
    }

    public function addView(View $view): self
    {
        if (!$this->views->contains($view)) {
            $this->views[] = $view;
            $view->setJobAnnouncement($this);
        }

        return $this;
    }

    public function removeView(View $view): self
    {
        if ($this->views->contains($view)) {
            $this->views->removeElement($view);
            // set the owning side to null (unless already changed)
            if ($view->getJobAnnouncement() === $this) {
                $view->setJobAnnouncement(null);
            }
        }

        return $this;
    }

    public function getWageRangeDependsOnQualifications(): ?bool
    {
        return $this->wageRangeDependsOnQualifications;
    }

    public function setWageRangeDependsOnQualifications(bool $wageRangeDependsOnQualifications): self
    {
        $this->wageRangeDependsOnQualifications = $wageRangeDependsOnQualifications;

        return $this;
    }

    public function getIsAlert(): bool {
        return $this->isAlert;
    }

    public function setIsAlert(bool $isAlert): self {
        $this->isAlert = $isAlert;

        return $this;
    }

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(?string $street): self
    {
        $this->street = $street;

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

    public function getState(): ?State
    {
        return $this->state;
    }

    public function setState(State $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function getZipcode(): ?string
    {
        return $this->zipcode;
    }

    public function setZipcode(string $zipcode): self
    {
        $this->zipcode = $zipcode;

        return $this;
    }

    /**
     * @return Collection|AlertedJobAnnouncement[]
     */
    public function getAlertedJobAnnouncements(): Collection
    {
        return $this->alertedJobAnnouncements;
    }

    public function addAlertedJobAnnouncement(AlertedJobAnnouncement $alertedJobAnnouncement): self
    {
        if (!$this->alertedJobAnnouncements->contains($alertedJobAnnouncement)) {
            $this->alertedJobAnnouncements[] = $alertedJobAnnouncement;
            $alertedJobAnnouncement->setJobAnnouncement($this);
        }

        return $this;
    }

    public function removeAlertedJobAnnouncement(AlertedJobAnnouncement $alertedJobAnnouncement): self
    {
        if ($this->alertedJobAnnouncements->contains($alertedJobAnnouncement)) {
            $this->alertedJobAnnouncements->removeElement($alertedJobAnnouncement);
            // set the owning side to null (unless already changed)
            if ($alertedJobAnnouncement->getJobAnnouncement() === $this) {
                $alertedJobAnnouncement->setJobAnnouncement(null);
            }
        }

        return $this;
    }

    public function getIsPostedByCGJ(): ?bool
    {
        return $this->isPostedByCGJ;
    }

    public function setIsPostedByCGJ(bool $isPostedByCGJ): self
    {
        $this->isPostedByCGJ = $isPostedByCGJ;

        return $this;
    }

    public function getJobTitleCity(): ?City
    {
        return $this->jobTitleCity;
    }

    public function setJobTitleCity(?City $jobTitleCity): self
    {
        $this->jobTitleCity = $jobTitleCity;

        return $this;
    }

    public function getHasNoEndDate(): ?bool
    {
        return $this->hasNoEndDate;
    }

    public function setHasNoEndDate(bool $hasNoEndDate): self
    {
        $this->hasNoEndDate = $hasNoEndDate;

        return $this;
    }

    public function getLastTestedDate(): ?\DateTimeInterface
    {
        return $this->lastTestedDate;
    }

    public function setLastTestedDate(?\DateTimeInterface $lastTestedDate): self
    {
        $this->lastTestedDate = $lastTestedDate;

        return $this;
    }

    public function getEndDateDescription(): ?string
    {
        return $this->endDateDescription;
    }

    public function setEndDateDescription(?string $endDateDescription): self
    {
        $this->endDateDescription = $endDateDescription;

        return $this;
    }

    public function getJobAnnouncementURL()
    {
        return $host = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/city/' . $this->jobTitle->getCity()->getSlug() . '/job/announcements/' . $this->id . '/full-view';
    }

    /**
     * @return Collection|JobAnnouncementImpression[]
     */
    public function getJobAnnouncementImpressions(): Collection
    {
        return $this->jobAnnouncementImpressions;
    }

    public function addJobAnnouncementImpression(JobAnnouncementImpression $jobAnnouncementImpression): self
    {
        if (!$this->jobAnnouncementImpressions->contains($jobAnnouncementImpression)) {
            $this->jobAnnouncementImpressions[] = $jobAnnouncementImpression;
            $jobAnnouncementImpression->setJobAnnouncement($this);
        }

        return $this;
    }

    public function removeJobAnnouncementImpression(JobAnnouncementImpression $jobAnnouncementImpression): self
    {
        if ($this->jobAnnouncementImpressions->contains($jobAnnouncementImpression)) {
            $this->jobAnnouncementImpressions->removeElement($jobAnnouncementImpression);
            // set the owning side to null (unless already changed)
            if ($jobAnnouncementImpression->getJobAnnouncement() === $this) {
                $jobAnnouncementImpression->setJobAnnouncement(null);
            }
        }

        return $this;
    }

}
