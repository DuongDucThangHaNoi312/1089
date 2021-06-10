<?php

namespace App\Entity\User;

use App\Entity\AlertedJobAnnouncement;
use App\Entity\City;
use App\Entity\City\County;
use App\Entity\JobAnnouncement;
use App\Entity\JobTitle\Lookup\JobCategory;
use App\Entity\JobTitle\Lookup\JobLevel;
use App\Entity\JobTitle\Lookup\JobTitleName;
use App\Entity\JobTitle\Lookup\JobType;
use App\Entity\User;
use App\Entity\User\JobSeekerUser\DismissedJobTitle;
use App\Entity\User\JobSeekerUser\Resume;
use App\Entity\User\JobSeekerUser\SavedJobAnnouncement;
use App\Entity\User\JobSeekerUser\SavedJobTitle;
use App\Entity\User\JobSeekerUser\SubmittedJobTitleInterest;
use App\Entity\User\JobSeekerUser\Subscription;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Validator\Constraints as CGJ;

/**
 * @ORM\Entity(repositoryClass="App\Repository\User\JobSeekerUserRepository")
 * @CGJ\JobSeekerRegistrationStep3Constraint()
 */
class JobSeekerUser extends User
{
    const NOTIFICATION_PREFERENCE_DAILY = 'daily';
    const NOTIFICATION_PREFERENCE_WEEKLY = 'weekly';
    const NOTIFICATION_PREFERENCE_MONTHLY = 'monthly';
    const NOTIFICATION_PREFERENCE_NONE = 'none';

    /**
     * @var City
     * @ORM\ManyToOne(targetEntity="App\Entity\City")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $city;

    /**
     * @var City\State
     * @ORM\ManyToOne(targetEntity="App\Entity\City\State")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $state;

    /**
     * @var County
     * @ORM\ManyToOne(targetEntity="App\Entity\City\County")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $county;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $zipcode;

    /**
     * @ORM\Column(type="boolean")
     */
    private $workForCityGovernment;

    /**
     * @var City
     * @ORM\ManyToOne(targetEntity="App\Entity\City")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $worksForCity;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $currentJobTitle;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\JobTitle\Lookup\JobType")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $interestedJobType;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\JobTitle\Lookup\JobLevel")
     */
    private $interestedJobLevels;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\JobTitle\Lookup\JobCategory")
     */
    private $interestedJobCategories;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\JobTitle\Lookup\JobTitleName")
     */
    private $interestedJobTitleNames;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\City\County", inversedBy="interestedJobSeekers")
     */
    private $interestedCounties;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\User\JobSeekerUser\Resume", mappedBy="jobSeeker", cascade={"persist", "remove"})
     */
    private $resume;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\User\JobSeekerUser\SavedJobAnnouncement", mappedBy="jobSeekerUser", orphanRemoval=true)
     */
    private $savedJobAnnouncements;

    /**
     * @var ArrayCollection
     * @var User\JobSeekerUser\DismissedJobAnnouncement
     * @ORM\OneToMany(targetEntity="App\Entity\User\JobSeekerUser\DismissedJobAnnouncement", mappedBy="jobSeekerUser", orphanRemoval=true)
     */
    private $dismissedJobAnnouncements;

    /**
     * @var User\JobSeekerUser\DismissedJobTitle
     * @ORM\OneToMany(targetEntity="App\Entity\User\JobSeekerUser\DismissedJobTitle", mappedBy="jobSeekerUser", orphanRemoval=true)
     */
    private $dismissedJobTitles;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\User\JobSeekerUser\SavedJobTitle", mappedBy="jobSeekerUser", orphanRemoval=true)
     */
    private $savedJobTitles;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\User\JobSeekerUser\SubmittedJobTitleInterest", mappedBy="jobSeekerUser", orphanRemoval=true)
     */
    private $submittedJobTitleInterests;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\User\JobSeekerUser\Subscription", mappedBy="jobSeekerUser", cascade={"persist", "remove"})
     */
    private $subscription;

    /**
     * @var County
     * @ORM\ManyToOne(targetEntity="App\Entity\City\County")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $worksForCounty;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\AlertedJobAnnouncement", mappedBy="jobSeeker", orphanRemoval=true)
     */
    private $alertedJobAnnouncements;

    /**
     * @ORM\Column(type="boolean")
     */
    private $receiveAlertsForSubmittedInterest = true;

    /**
      * @ORM\Column(type="boolean")
     */
    private $receiveAlertsForJobsMatchingSavedSearchCriteria = true;

    /**
     * @ORM\Column(type="string", length=10)
     */
    private $notificationPreferenceForSubmittedInterest = self::NOTIFICATION_PREFERENCE_DAILY;

    /**
     * @ORM\Column(type="string", length=10)
     */
    private $notificationPreferenceForJobsMatchingSavedSearchCriteria = self::NOTIFICATION_PREFERENCE_DAILY;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\User\UserLogin", mappedBy="user", orphanRemoval=true)
     */
    private $userLogins;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $loginFrequency;

    public function isAllowedLevel(JobLevel $jobLevel)
    {
        return $this->getSubscription()->getSubscriptionPlan()->getAllowedJobLevels()->contains($jobLevel);
    }

    public function hasSavedJobAnnouncement(JobAnnouncement $jobAnnouncement)
    {
        foreach ($this->getSavedJobAnnouncements() as $savedJobAnnouncement) {
            if ($savedJobAnnouncement->getJobAnnouncement() == $jobAnnouncement) {
                return true;
            }
        }
        return false;
    }

    public function hasSavedJobAnnouncementById($jobAnnouncementId)
    {
        foreach ($this->getSavedJobAnnouncements() as $savedJobAnnouncement) {
            if ($savedJobAnnouncement->getJobAnnouncement()->getId() == $jobAnnouncementId) {
                return true;
            }
        }
        return false;
    }

    public function getCountUniqueCitiesSubmittedInterest()
    {
        $cities = [];
        foreach ($this->getSubmittedJobTitleInterests() as $i) {
            $cities[$i->getJobTitle()->getCity()->getId()] = true;
        }
        return count(array_keys($cities));
    }

    public function removeAllSubmittedJobTitleInterest()
    {
        foreach ($this->getSubmittedJobTitleInterests() as $interest) {
            $this->removeSubmittedJobTitleInterest($interest);
        }
        return $this;
    }

    public function isResumeComplete()
    {
        return ($this->getResume() && $this->getResume()->getIsComplete());
    }

    public function hasResume() {
        return  $this->getResume() ? true : false;
    }

    public function __construct()
    {
        parent::__construct();
        $this->interestedJobLevels        = new ArrayCollection();
        $this->interestedJobCategories    = new ArrayCollection();
        $this->interestedCounties         = new ArrayCollection();
        $this->savedJobAnnouncements      = new ArrayCollection();
        $this->savedJobTitles             = new ArrayCollection();
        $this->submittedJobTitleInterests = new ArrayCollection();
        $this->dismissedJobAnnouncements  = new ArrayCollection();
        $this->dismissedJobTitles         = new ArrayCollection();
        $this->interestedJobTitleNames    = new ArrayCollection();
        $this->alertedJobAnnouncements    = new ArrayCollection();
        $this->userLogins                 = new ArrayCollection();
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

    public function getState(): ?City\State
    {
        return $this->state;
    }

    public function setState(?City\State $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function getCounty(): ?County
    {
        return $this->county;
    }

    public function setCounty(?County $county): self
    {
        $this->county = $county;

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

    public function getWorkForCityGovernment(): ?bool
    {
        return $this->workForCityGovernment;
    }

    public function setWorkForCityGovernment(bool $workForCityGovernment): self
    {
        $this->workForCityGovernment = $workForCityGovernment;

        return $this;
    }

    public function getCurrentJobTitle(): ?string
    {
        return $this->currentJobTitle;
    }

    public function setCurrentJobTitle(?string $currentJobTitle): self
    {
        $this->currentJobTitle = $currentJobTitle;

        return $this;
    }

    public function setInterestedJobType(JobType $interestedJobType): self
    {
        $this->interestedJobType = $interestedJobType;

        return $this;
    }

    public function getInterestedJobType(): ?JobType
    {
        return $this->interestedJobType;
    }

    /**
     * @return Collection|JobLevel[]
     */
    public function getInterestedJobLevels(): Collection
    {
        return $this->interestedJobLevels;
    }

    public function setInterestedJobLevels($interestedJobLevels)
    {
        $this->interestedJobLevels = $interestedJobLevels;
        return $this;
    }

    public function addInterestedJobLevels(JobLevel $interestedJobLevel): self
    {
        if (!$this->interestedJobLevels->contains($interestedJobLevel)) {
            $this->interestedJobLevels[] = $interestedJobLevel;
        }

        return $this;
    }

    public function removeInterestedJobLevel(JobLevel $interestedJobLevel): self
    {
        if ($this->interestedJobLevels->contains($interestedJobLevel)) {
            $this->interestedJobLevels->removeElement($interestedJobLevel);
        }

        return $this;
    }


    public function setInterestedJobCategories($interestedJobCategories)
    {
        $this->interestedJobCategories = $interestedJobCategories;
        return $this;
    }


    /**
     * @return Collection|JobCategory[]
     */
    public function getInterestedJobCategories(): Collection
    {
        return $this->interestedJobCategories;
    }

    public function addInterestedJobCategory(JobCategory $interestedJobCategory): self
    {
        if (!$this->interestedJobCategories->contains($interestedJobCategory)) {
            $this->interestedJobCategories[] = $interestedJobCategory;
        }

        return $this;
    }

    public function removeInterestedJobCategory(JobCategory $interestedJobCategory): self
    {
        if ($this->interestedJobCategories->contains($interestedJobCategory)) {
            $this->interestedJobCategories->removeElement($interestedJobCategory);
        }

        return $this;
    }

    /**
     * @return Collection|County[]
     */
    public function getInterestedCounties(): Collection
    {
        return $this->interestedCounties;
    }

    public function setInterestedCounties($interestedCounties)
    {
        $this->interestedCounties = $interestedCounties;
        return $this;
    }

    public function addInterestedCounty(County $interestedCounty): self
    {
        if (!$this->interestedCounties->contains($interestedCounty)) {
            $this->interestedCounties[] = $interestedCounty;
        }

        return $this;
    }

    public function removeInterestedCounty(County $interestedCounty): self
    {
        if ($this->interestedCounties->contains($interestedCounty)) {
            $this->interestedCounties->removeElement($interestedCounty);
        }

        return $this;
    }

    public function getResume(): ?Resume
    {
        return $this->resume;
    }

    public function setResume(?Resume $resume): self
    {
        $this->resume = $resume;

        // set (or unset) the owning side of the relation if necessary
        $newJobSeeker = $resume === null ? null : $this;
        if ($newJobSeeker !== $resume->getJobSeeker()) {
            $resume->setJobSeeker($newJobSeeker);
        }

        return $this;
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
        if (!$this->savedJobAnnouncements->contains($savedJobAnnouncement)) {
            $this->savedJobAnnouncements[] = $savedJobAnnouncement;
            $savedJobAnnouncement->setJobSeekerUser($this);
        }

        return $this;
    }

    public function removeSavedJobAnnouncement(SavedJobAnnouncement $savedJobAnnouncement): self
    {
        if ($this->savedJobAnnouncements->contains($savedJobAnnouncement)) {
            $this->savedJobAnnouncements->removeElement($savedJobAnnouncement);
            // set the owning side to null (unless already changed)
            if ($savedJobAnnouncement->getJobSeekerUser() === $this) {
                $savedJobAnnouncement->setJobSeekerUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|User\JobSeekerUser\DismissedJobAnnouncement[]
     */
    public function getDismissedJobAnnouncements(): Collection
    {
        return $this->dismissedJobAnnouncements;
    }

    public function addDismissedJobAnnouncement(User\JobSeekerUser\DismissedJobAnnouncement $dismissedJobAnnouncement): self
    {
        if (!$this->dismissedJobAnnouncements->contains($dismissedJobAnnouncement)) {
            $this->dismissedJobAnnouncements[] = $dismissedJobAnnouncement;
            $dismissedJobAnnouncement->setJobSeekerUser($this);
        }

        return $this;
    }

    public function removeDismissedJobAnnouncement(User\JobSeekerUser\DismissedJobAnnouncement $dismissedJobAnnouncement): self
    {
        if ($this->dismissedJobAnnouncements->contains($dismissedJobAnnouncement)) {
            $this->dismissedJobAnnouncements->removeElement($dismissedJobAnnouncement);
            // set the owning side to null (unless already changed)
            if ($dismissedJobAnnouncement->getJobSeekerUser() === $this) {
                $dismissedJobAnnouncement->setJobSeekerUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|User\JobSeekerUser\DismissedJobAnnouncement[]
     */
    public function getDismissedJobTitles(): Collection
    {
        return $this->dismissedJobTitles;
    }

    public function addDismissedJobTitles(User\JobSeekerUser\DismissedJobTitle $dismissedJobTitle): self
    {
        if (!$this->dismissedJobTitles->contains($dismissedJobTitle)) {
            $this->dismissedJobTitles[] = $dismissedJobTitle;
            $dismissedJobTitle->setJobSeekerUser($this);
        }

        return $this;
    }

    public function removeDismissedJobTitles(User\JobSeekerUser\DismissedJobTitle $dismissedJobTitle): self
    {
        if ($this->dismissedJobTitles->contains($dismissedJobTitle)) {
            $this->dismissedJobTitles->removeElement($dismissedJobTitle);
            // set the owning side to null (unless already changed)
            if ($dismissedJobTitle->getJobSeekerUser() === $this) {
                $dismissedJobTitle->setJobSeekerUser(null);
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
            $savedJobTitle->setJobSeekerUser($this);
        }

        return $this;
    }

    public function removeSavedJobTitle(SavedJobTitle $savedJobTitle): self
    {
        if ($this->savedJobTitles->contains($savedJobTitle)) {
            $this->savedJobTitles->removeElement($savedJobTitle);
            // set the owning side to null (unless already changed)
            if ($savedJobTitle->getJobSeekerUser() === $this) {
                $savedJobTitle->setJobSeekerUser(null);
            }
        }

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
            $submittedJobTitleInterest->setJobSeekerUser($this);
        }

        return $this;
    }

    public function removeSubmittedJobTitleInterest(SubmittedJobTitleInterest $submittedJobTitleInterest): self
    {
        if ($this->submittedJobTitleInterests->contains($submittedJobTitleInterest)) {
            $this->submittedJobTitleInterests->removeElement($submittedJobTitleInterest);
            // set the owning side to null (unless already changed)
            if ($submittedJobTitleInterest->getJobSeekerUser() === $this) {
                $submittedJobTitleInterest->setJobSeekerUser(null);
            }
        }

        return $this;
    }

    public function getSubscription(): ?Subscription
    {
        return $this->subscription;
    }

    public function setSubscription(Subscription $subscription): self
    {
        $this->subscription = $subscription;

        // set the owning side of the relation if necessary
        if ($this !== $subscription->getJobSeekerUser()) {
            $subscription->setJobSeekerUser($this);
        }

        return $this;
    }

    public function addDismissedJobTitle(DismissedJobTitle $dismissedJobTitle): self
    {
        if (!$this->dismissedJobTitles->contains($dismissedJobTitle)) {
            $this->dismissedJobTitles[] = $dismissedJobTitle;
            $dismissedJobTitle->setJobSeekerUser($this);
        }

        return $this;
    }

    public function removeDismissedJobTitle(DismissedJobTitle $dismissedJobTitle): self
    {
        if ($this->dismissedJobTitles->contains($dismissedJobTitle)) {
            $this->dismissedJobTitles->removeElement($dismissedJobTitle);
            // set the owning side to null (unless already changed)
            if ($dismissedJobTitle->getJobSeekerUser() === $this) {
                $dismissedJobTitle->setJobSeekerUser(null);
            }
        }

        return $this;
    }

    public function setInterestedJobTitleNames($interestedJobTitleNames)
    {
        $this->interestedJobTitleNames = $interestedJobTitleNames;
        return $this;
    }

    /**
     * @return Collection|JobTitleName[]
     */
    public function getInterestedJobTitleNames(): Collection
    {
        return $this->interestedJobTitleNames;
    }

    public function addInterestedJobTitleName(JobTitleName $interestedJobTitleName): self
    {
        if (!$this->interestedJobTitleNames->contains($interestedJobTitleName)) {
            $this->interestedJobTitleNames[] = $interestedJobTitleName;
        }

        return $this;
    }

    public function removeInterestedJobTitleName(JobTitleName $interestedJobTitleName): self
    {
        if ($this->interestedJobTitleNames->contains($interestedJobTitleName)) {
            $this->interestedJobTitleNames->removeElement($interestedJobTitleName);
        }

        return $this;
    }

    public function getWorksForCity(): ?City
    {
        return $this->worksForCity;
    }

    public function setWorksForCity(?City $worksForCity): self
    {
        $this->worksForCity = $worksForCity;

        return $this;
    }

    public function getWorksForCounty(): ?County
    {
        return $this->worksForCounty;
    }

    public function setWorksForCounty(?County $worksForCounty): self
    {
        $this->worksForCounty = $worksForCounty;

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
            $alertedJobAnnouncement->setJobSeeker($this);
        }

        return $this;
    }

    public function removeAlertedJobAnnouncement(AlertedJobAnnouncement $alertedJobAnnouncement): self
    {
        if ($this->alertedJobAnnouncements->contains($alertedJobAnnouncement)) {
            $this->alertedJobAnnouncements->removeElement($alertedJobAnnouncement);
            // set the owning side to null (unless already changed)
            if ($alertedJobAnnouncement->getJobSeeker() === $this) {
                $alertedJobAnnouncement->setJobSeeker(null);
            }
        }

        return $this;
    }

    public function getReceiveAlertsForSubmittedInterest(): ?bool
    {
        return $this->receiveAlertsForSubmittedInterest;
    }

    public function setReceiveAlertsForSubmittedInterest(bool $receiveAlertsForSubmittedInterest): self
    {
        $this->receiveAlertsForSubmittedInterest = $receiveAlertsForSubmittedInterest;

        return $this;
    }

    public function getReceiveAlertsForJobsMatchingSavedSearchCriteria(): ?bool
    {
        return $this->receiveAlertsForJobsMatchingSavedSearchCriteria;
    }

    public function setReceiveAlertsForJobsMatchingSavedSearchCriteria(bool $receiveAlertsForJobsMatchingSavedSearchCriteria): self
    {
        $this->receiveAlertsForJobsMatchingSavedSearchCriteria = $receiveAlertsForJobsMatchingSavedSearchCriteria;

        return $this;
    }

    public function getNotificationPreferenceForSubmittedInterest(): ?string
    {
        return $this->notificationPreferenceForSubmittedInterest;
    }

    public function setNotificationPreferenceForSubmittedInterest(string $notificationPreferenceForSubmittedInterest): self
    {
        $this->notificationPreferenceForSubmittedInterest = $notificationPreferenceForSubmittedInterest;

        return $this;
    }

    public function getNotificationPreferenceForJobsMatchingSavedSearchCriteria(): ?string
    {
        return $this->notificationPreferenceForJobsMatchingSavedSearchCriteria;
    }

    public function setNotificationPreferenceForJobsMatchingSavedSearchCriteria(string $notificationPreferenceForJobsMatchingSavedSearchCriteria): self
    {
        $this->notificationPreferenceForJobsMatchingSavedSearchCriteria = $notificationPreferenceForJobsMatchingSavedSearchCriteria;

        return $this;
    }

    /**
     * @return Collection|UserLogin[]
     */
    public function getUserLogins(): Collection
    {
        return $this->userLogins;
    }

    public function addUserLogin(UserLogin $userLogin): self
    {
        if (!$this->userLogins->contains($userLogin)) {
            $this->userLogins[] = $userLogin;
            $userLogin->setUser($this);
        }

        return $this;
    }

    public function removeUserLogin(UserLogin $userLogin): self
    {
        if ($this->userLogins->contains($userLogin)) {
            $this->userLogins->removeElement($userLogin);
            // set the owning side to null (unless already changed)
            if ($userLogin->getUser() === $this) {
                $userLogin->setUser(null);
            }
        }

        return $this;
    }

    public function getLoginFrequency(): ?float
    {
        return $this->loginFrequency;
    }

    public function setLoginFrequency(?float $loginFrequency): self
    {
        $this->loginFrequency = $loginFrequency;

        return $this;
    }

}
