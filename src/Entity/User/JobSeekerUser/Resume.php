<?php

namespace App\Entity\User\JobSeekerUser;

use App\Entity\City;
use App\Entity\City\State;
use App\Entity\JobTitle\Lookup\JobTitleName;
use App\Entity\Resume\Education;
use App\Entity\Resume\LicenseCertification;
use App\Entity\Resume\WorkHistory;
use App\Entity\City\County;
use App\Entity\City\JobTitle;
use App\Entity\JobTitle\Lookup\JobCategory;
use App\Entity\JobTitle\Lookup\JobLevel;
use App\Entity\JobTitle\Lookup\JobType;
use App\Entity\User\CityUser\SavedResume;
use App\Entity\User\JobSeekerUser;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Traits\BlameableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\User\JobSeekerUser\ResumeRepository")
 */
class Resume
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
     * @ORM\OneToOne(targetEntity="App\Entity\User\JobSeekerUser", inversedBy="resume", cascade={"persist"})
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     */
    private $jobSeeker;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Assert\NotBlank(groups={"job_seeker_resume_summary"})
     */
    private $careerObjective;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=0, nullable=true)
     * @Assert\NotBlank(groups={"job_seeker_resume_key_qualifications"})
     */
    private $yearsWorkedInProfession;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=0, nullable=true)
     */
    private $yearsWorkedInCityGovernment;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @Assert\NotNull(groups={"job_seeker_resume_settings"})
     */
    private $isAvailableForSearch;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isComplete = false;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $highestEducationLevel;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\City", inversedBy="blockedResumes")
     * @ORM\JoinTable(name="cities_blocked")
     */
    private $citiesToBlock;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Resume\WorkHistory", mappedBy="resume", cascade={"persist"}, orphanRemoval=true)
     */
    private $workHistories;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Resume\LicenseCertification", mappedBy="resume",cascade={"persist"}, orphanRemoval=true)
     */
    private $licenseCertifications;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Resume\Education", mappedBy="resume", cascade={"persist"}, orphanRemoval=true)
     */
    private $education;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=false)
     * @Assert\NotBlank(groups={"job_seeker_resume_job_seeker"})
     */
    protected $firstname;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=false)
     * @Assert\NotBlank(groups={"job_seeker_resume_job_seeker"})
     */
    protected $lastname;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     * @Assert\NotBlank(groups={"job_seeker_resume_job_seeker"})
     */
    protected $phone;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=false)
     * @Assert\NotBlank(groups={"job_seeker_resume_job_seeker"})
     */
    protected $email;

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
     * @Assert\NotBlank(groups={"job_seeker_resume_key_qualifications"})
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
     * @var Collection|JobTitleName[]
     * @ORM\ManyToMany(targetEntity="App\Entity\JobTitle\Lookup\JobTitleName")
     */
    private $interestedJobTitleNames;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\City\County")
     */
    private $interestedCounties;

    public function isCityBlocked(City $city)
    {
        return $this->getCitiesToBlock()->contains($city);
    }

    public function getHighestEducationLevel(): ?String {
        $highestDegree = null;
        $highestDegreeValue = 0;
        foreach ($this->getEducation() as $degree) {
            $degreeValue = 0;
            $degreeAbbreviation = '';
            switch($degree->getDegreeType()->getSlug()) {
                case 'high-school-diploma':
                    $degreeValue = 1;
                    $degreeAbbreviation = 'HS';
                    break;
                case 'associate-degree':
                    $degreeValue = 2;
                    $degreeAbbreviation = 'AA';
                    break;
                case 'bachelors-of-science-degree':
                    $degreeValue = 3;
                    $degreeAbbreviation = 'BS';
                    break;
                case 'bachelors-of-arts-degree':
                    $degreeValue = 3;
                    $degreeAbbreviation = 'BA';
                    break;
                case 'masters-of-arts-degree':
                    $degreeValue = 4;
                    $degreeAbbreviation = 'MA';
                    break;
                case 'masters-of-science-degree':
                    $degreeValue = 4;
                    $degreeAbbreviation = 'MS';
                    break;
                case 'phd-degree':
                    $degreeValue = 5;
                    $degreeAbbreviation = 'PhD';
                    break;
            }

            if ($highestDegreeValue < $degreeValue) {
                $highestDegreeValue = $degreeValue;
                $highestDegree = $degreeAbbreviation;
            }
        }

        return $highestDegree;
    }

    public function getFullName() {
        return (string) $this->firstname . " " . (string) $this->lastname;
    }

    public function checkIsComplete() {
        /** Settings */
        if ($this->getIsAvailableForSearch() === null) {
            return false;
        }

        /** Summary */
        if ($this->getCareerObjective() == null) {
            return false;
        }

        /** Interest Profile */
        if ($this->getInterestedJobType() == null) {
            return false;
        }

        if (count($this->getInterestedJobLevels()) == 0) {
            return false;
        }

        if (count($this->getInterestedJobCategories()) == 0) {
            return false;
        }

        if (count($this->getInterestedJobTitleNames()) == 0) {
            return false;
        }

        if (count($this->getInterestedCounties()) == 0) {
            return false;
        }

        /** Key Qualifications */
        if ($this->getYearsWorkedInProfession() == null) {
            return false;
        }

        if (count($this->getEducation()) == 0) {
            return false;
        }

        /** Contact Information */
        if ($this->getFirstname() == '') {
            return false;
        }

        if ($this->getLastname() == '') {
            return false;
        }

        if ($this->getEmail() == '') {
            return false;
        }

        if ($this->getCity() == null) {
            return false;
        }

        if ($this->getState() == null) {
            return false;
        }

        return true;
    }

    public function percentageComplete() {
        $sections = ['contact', 'summary', 'settings', 'interest-profile', 'key-qualifications', 'work-history'];
        $countSectionsComplete = count($sections);
        foreach ($sections as $section) {
            if (!$this->isSectionComplete($section)) {
                $countSectionsComplete -= 1;
            }
        }
        return (int)$countSectionsComplete/count($sections)*100;
    }

    public function isSectionComplete(?string $section) :?bool {
        if (!$section) {
            return null;
        }
        $isComplete = true;
        switch ($section) {
            case "contact":
                if ($this->getFirstname() == '') {
                    $isComplete = false;
                }

                if ($this->getLastname() == '') {
                    $isComplete =  false;
                }

                if ($this->getEmail() == '') {
                    $isComplete = false;
                }

                if ($this->getCity() == null) {
                    $isComplete = false;
                }

                if ($this->getState() == null) {
                    $isComplete = false;
                }
                break;
            case "summary":
                if ($this->getCareerObjective() == null) {
                    $isComplete =  false;
                }
                break;
            case "settings":
                if ($this->getIsAvailableForSearch() === null) {
                    $isComplete = false;
                }
                break;
            case "interest-profile":
                if ($this->getInterestedJobType() == null) {
                    $isComplete = false;
                }

                if (count($this->getInterestedJobLevels()) == 0) {
                    $isComplete = false;
                }

                if (count($this->getInterestedJobCategories()) == 0) {
                    $isComplete = false;
                }

                if (count($this->getInterestedJobTitleNames()) == 0) {
                    $isComplete = false;
                }

                if (count($this->getInterestedCounties()) == 0) {
                    $isComplete =  false;
                }
                break;
            case "key-qualifications":
                if ($this->yearsWorkedInProfession == null) {
                    $isComplete =  false;
                }

                if (count($this->education) == 0) {
                    $isComplete =  false;
                }
                break;
            default:
                break;
        }
        return $isComplete;
    }

    public function setInverseSide() {
        foreach ($this->getEducation() as $education) {
            $education->setResume($this);
        }

        foreach($this->getLicenseCertifications() as $licenseCertification) {
            $licenseCertification->setResume($this);
        }

        foreach ($this->workHistories as $workHistory) {
            $workHistory->setResume($this);
        }
    }

    public function setInitialJobSeekerFields(JobSeekerUser $jobSeekerUser) {
        $this->setFirstname($jobSeekerUser->getFirstname());
        $this->setLastname($jobSeekerUser->getLastname());
        $this->setEmail($jobSeekerUser->getEmail());
        $this->setPhone($jobSeekerUser->getPhone());
        $this->setCity($jobSeekerUser->getCity());
        $this->setState($jobSeekerUser->getState());
        $this->setInterestedJobType($jobSeekerUser->getInterestedJobType());
        $this->setCurrentJobTitle($jobSeekerUser->getCurrentJobTitle());

        foreach ($jobSeekerUser->getInterestedJobLevels() as $interestedJobLevel) {
            $this->addInterestedJobLevel($interestedJobLevel);
        }

        foreach ($jobSeekerUser->getInterestedJobCategories() as $interestedJobCategory) {
            $this->addInterestedJobCategory($interestedJobCategory);
        }

        foreach($jobSeekerUser->getInterestedCounties() as $interestedCounty) {
            $this->addInterestedCounty($interestedCounty);
        }

        foreach($jobSeekerUser->getInterestedJobTitleNames() as $interestedJobTitle) {
            $this->addInterestedJobTitleName($interestedJobTitle);
        }
    }

    static public function create(JobSeekerUser $user): Resume {
        $resume = new Resume();
        $resume->setJobSeeker($user);
        $resume->setInitialJobSeekerFields($user);
        return $resume;
    }

    public function __construct()
    {
        $this->interestedJobLevels = new ArrayCollection();
        $this->interestedJobCategories = new ArrayCollection();
        $this->interestedJobTitleNames = new ArrayCollection();
        $this->interestedCounties = new ArrayCollection();
        $this->citiesToBlock = new ArrayCollection();
        $this->workHistories = new ArrayCollection();
        $this->licenseCertifications = new ArrayCollection();
        $this->education = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCareerObjective(): ?string
    {
        return $this->careerObjective;
    }

    public function setCareerObjective(?string $careerObjective): self
    {
        $this->careerObjective = $careerObjective;

        return $this;
    }

    public function getYearsWorkedInProfession()
    {
        return $this->yearsWorkedInProfession;
    }

    public function setYearsWorkedInProfession($yearsWorkedInProfession): self
    {
        $this->yearsWorkedInProfession = $yearsWorkedInProfession;

        return $this;
    }

    public function getYearsWorkedInCityGovernment()
    {
        return $this->yearsWorkedInCityGovernment;
    }

    public function setYearsWorkedInCityGovernment($yearsWorkedInCityGovernment): self
    {
        $this->yearsWorkedInCityGovernment = $yearsWorkedInCityGovernment;

        return $this;
    }

    public function getIsAvailableForSearch(): ?bool
    {
        return $this->isAvailableForSearch;
    }

    public function setIsAvailableForSearch(bool $isAvailableForSearch): self
    {
        $this->isAvailableForSearch = $isAvailableForSearch;

        return $this;
    }

    public function getIsComplete(): ?bool
    {
        return $this->isComplete;
    }

    public function setIsComplete(bool $isComplete): self
    {
        $this->isComplete = $isComplete;

        return $this;
    }

    public function getJobSeeker(): ?JobSeekerUser
    {
        return $this->jobSeeker;
    }

    public function setJobSeeker(JobSeekerUser $jobSeeker): self
    {
        $this->jobSeeker = $jobSeeker;

        return $this;
    }

    /**
     * @return Collection|City[]
     */
    public function getCitiesToBlock(): Collection
    {
        return $this->citiesToBlock;
    }

    public function addCitiesToBlock(City $citiesToBlock): self
    {
        if (!$this->citiesToBlock->contains($citiesToBlock)) {
            $this->citiesToBlock[] = $citiesToBlock;
        }

        return $this;
    }

    public function removeCitiesToBlock(City $citiesToBlock): self
    {
        if ($this->citiesToBlock->contains($citiesToBlock)) {
            $this->citiesToBlock->removeElement($citiesToBlock);
        }

        return $this;
    }

    /**
     * @return Collection|WorkHistory[]
     */
    public function getWorkHistories(): Collection
    {
        return $this->workHistories;
    }

    public function addWorkHistory(WorkHistory $workHistory): self
    {
        if (!$this->workHistories->contains($workHistory)) {
            $this->workHistories[] = $workHistory;
            $workHistory->setResume($this);
        }

        return $this;
    }

    public function removeWorkHistory(WorkHistory $workHistory): self
    {
        if ($this->workHistories->contains($workHistory)) {
            $this->workHistories->removeElement($workHistory);
            // set the owning side to null (unless already changed)
            if ($workHistory->getResume() === $this) {
                $workHistory->setResume(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|LicenseCertification[]
     */
    public function getLicenseCertifications(): Collection
    {
        return $this->licenseCertifications;
    }

    public function addLicenseCertification(LicenseCertification $licenseCertification): self
    {
        if (!$this->licenseCertifications->contains($licenseCertification)) {
            $this->licenseCertifications[] = $licenseCertification;
            $licenseCertification->setResume($this);
        }

        return $this;
    }

    public function removeLicenseCertification(LicenseCertification $licenseCertification): self
    {
        if ($this->licenseCertifications->contains($licenseCertification)) {
            $this->licenseCertifications->removeElement($licenseCertification);
            // set the owning side to null (unless already changed)
            if ($licenseCertification->getResume() === $this) {
                $licenseCertification->setResume(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Education[]
     */
    public function getEducation(): Collection
    {
        return $this->education;
    }

    public function addEducation(Education $education): self
    {
        if (!$this->education->contains($education)) {
            $this->education[] = $education;
            $education->setResume($this);
        }

        return $this;
    }

    public function removeEducation(Education $education): self
    {
        if ($this->education->contains($education)) {
            $this->education->removeElement($education);
            // set the owning side to null (unless already changed)
            if ($education->getResume() === $this) {
                $education->setResume(null);
            }
        }

        return $this;
    }

//    public function getHighestEducationLevel(): ?string
//    {
//        return $this->highestEducationLevel;
//    }

    public function setHighestEducationLevel(string $highestEducationLevel): self
    {
        $this->highestEducationLevel = $highestEducationLevel;

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

    public function setState(?State $state): self
    {
        $this->state = $state;

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

    public function getInterestedJobType(): ?JobType
    {
        return $this->interestedJobType;
    }

    public function setInterestedJobType(?JobType $interestedJobType): self
    {
        $this->interestedJobType = $interestedJobType;

        return $this;
    }

    /**
     * @return Collection|JobLevel[]
     */
    public function getInterestedJobLevels(): Collection
    {
        return $this->interestedJobLevels;
    }

    public function addInterestedJobLevel(JobLevel $interestedJobLevel): self
    {
        if (!$this->interestedJobLevels->contains($interestedJobLevel)) {
            $this->interestedJobLevels[] = $interestedJobLevel;
        }

        return $this;
    }

    public function removeInterestedJobLevels(JobLevel $interestedJobLevel): self
    {
        if ($this->interestedJobLevels->contains($interestedJobLevel)) {
            $this->interestedJobLevels->removeElement($interestedJobLevel);
        }

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
     * @return Collection|JobTitleName[]
     */
    public function getInterestedJobTitleNames(): Collection
    {
        return $this->interestedJobTitleNames;
    }

    public function addInterestedJobTitleName(JobTitleName $interestedJobTitleName): self
    {
        if (!$this->getInterestedJobTitleNames()->contains($interestedJobTitleName)) {
            $this->interestedJobTitleNames[] = $interestedJobTitleName;
        }

        return $this;
    }

    public function removeInterestedJobTitleName(JobTitleName $interestedJobTitleName): self
    {
        if ($this->getInterestedJobTitleNames()->contains($interestedJobTitleName)) {
            $this->interestedJobTitleNames->removeElement($interestedJobTitleName);
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

    /**
     * {@inheritdoc}
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * {@inheritdoc}
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * {@inheritdoc}
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * {@inheritdoc}
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * {@inheritdoc}
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }


    /**
     * @return Collection|SavedResume[]
     */
    public function getSavedResumes(): Collection
    {
        return $this->savedResumes;
    }

    public function addSavedResume(SavedResume $savedResume): self
    {
        if (!$this->savedResumes->contains($savedResume)) {
            $this->savedResumes[] = $savedResume;
            $savedResume->setResume($this);
        }

        return $this;
    }

    public function removeSavedResume(SavedResume $savedResume): self
    {
        if ($this->savedResumes->contains($savedResume)) {
            $this->savedResumes->removeElement($savedResume);
            // set the owning side to null (unless already changed)
            if ($savedResume->getResume() === $this) {
                $savedResume->setResume(null);
            }
        }

        return $this;
    }


}
