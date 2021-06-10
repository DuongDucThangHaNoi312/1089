<?php

namespace App\Entity;

use App\Entity\City\CensusPopulation;
use App\Entity\City\Division;
use App\Entity\City\Lookup\ProfileType;
use App\Entity\City\County;
use App\Entity\City\JobTitle;
use App\Entity\City\Department;
use App\Entity\City\OperationHours;
use App\Entity\City\State;
use App\Entity\CityRegistration\Lookup\CityRegistrationStatus;
use App\Entity\City\Subscription;
use App\Entity\JobAnnouncement\Lookup\JobAnnouncementStatus;
use App\Entity\JobTitle\Lookup\JobTitleName;
use App\Entity\User\CityUser;
use App\Entity\User\JobSeekerUser\Resume;
use App\Entity\User\SavedCity;
use App\Repository\CityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Mapping as ORM;
use App\Traits\BlameableEntity;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity(repositoryClass="App\Repository\CityRepository")
 * @Vich\Uploadable
 */
class City
{
    const MAX_STARS = 5;

    use TimestampableEntity;
    use BlameableEntity;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $profileTitle;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $profileAbout;

    /**
     * @var string
     * @Gedmo\Slug(fields={"name"}, updatable=false)
     * @ORM\Column(type="string", length=255)
     */
    private $slug;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $prefix;

    /**
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\City\County", mappedBy="cities", cascade={"persist"})
     * @ORM\OrderBy({"name" = "ASC"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $counties;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $address;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $zipCode;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $passcode;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $cityHallPhone;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $timezone;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $timezoneSummer;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $hoursDescription;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $hoursDescriptionOther;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $yearFounded;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $yearChartered;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $yearIncorporated;

    /**
     * @ORM\Column(type="decimal", precision=8, scale=2, nullable=true)
     */
    private $squareMiles;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $countFTE;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $hrDirectorFirstName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $hrDirectorLastName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $hrNamePrefix;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $hrNameSuffix;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $hrDirectorTitle;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $hrDirectorPhone;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $hrDirectorEmail;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $mainWebsite;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $sealImage;

    /**
     * @Vich\UploadableField(mapping="city_seal_images", fileNameProperty="sealImage")
     * @var File
     */
    private $sealImageFile;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $bannerImage;

    /**
     * @Vich\UploadableField(mapping="city_banner_images", fileNameProperty="bannerImage")
     * @var File
     */
    private $bannerImageFile;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isRegistered = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $allowsJobAnnouncements = false;

    /**
     * @var \App\Entity\City\Lookup\ProfileType
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\City\Lookup\ProfileType")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $profileType;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isValidated = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $doesCityAllowChanges = false;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $profileAddedDate;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $jobTitlesAddedDate;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $countJobTitles;

    /**
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Url", mappedBy="city", cascade={"persist"}, orphanRemoval=true)
     */
    private $urls;

    /**
     *
     * @ORM\OneToMany(targetEntity="App\Entity\City\CensusPopulation", mappedBy="city", cascade={"persist"}, orphanRemoval=true)
     */
    private $censusPopulations;

    /**
     * ArrayCollection\OperationHours[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\City\OperationHours", mappedBy="city", cascade={"persist"}, orphanRemoval=true)
     */
    private $operationHours;

    /**
     *
     * @ORM\OneToMany(targetEntity="App\Entity\City\Department", mappedBy="city", cascade={"persist"}, orphanRemoval=true)
     * @ORM\OrderBy({"orderByNumber" = "ASC"})
     */
    private $departments;

    /**
     * ArrayCollection\JobTitle[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\City\JobTitle", mappedBy="city", cascade={"persist"})
     */
    private $jobTitles;

    /**
     * @var ArrayCollection|CityRegistration[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\CityRegistration", mappedBy="city", cascade={"persist"}, orphanRemoval=true)
     */
    private $cityRegistrations;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\CityCityUser", mappedBy="city", cascade={"remove", "persist"}, orphanRemoval=true)
     */
    private $cityCityUsers;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\User\JobSeekerUser\Resume", mappedBy="citiesToBlock")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $blockedResumes;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\City\Division", mappedBy="city", orphanRemoval=true)
     */
    private $divisions;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\City\Subscription", mappedBy="city", cascade={"persist", "remove"})
     * @ORM\JoinColumn(onDelete="SET NULL", nullable=true)
     */
    private $subscription;

    /**
     * @var \App\Entity\User\CityUser
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\User\CityUser")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $adminCityUser;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\User\SavedCity", mappedBy="city")
     */
    private $savedCities;

    /**
     * @var int
     *
     * Not a persisted property, calculated and hydrated on postLoad event
     * GLR currently not used, intentionally, since class is missing HasLifeCycleEvents annotation.
     */
    private $countOfUsersWhoSubmittedInterest;

    private $tempState;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isSuspended = false;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $suspensionEmailSentAt;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\JobTitle\Lookup\JobTitleName", mappedBy="createdByCity")
     */
    private $createdJobTitleNames;

    /**
     * @ORM\Column(type="integer")
     */
    private $currentStars = 0;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $cgjPostsJobs;

    public function __construct()
    {
        $this->counties = new ArrayCollection();
        $this->urls = new ArrayCollection();
        $this->censusPopulations = new ArrayCollection();
        $this->operationHours = new ArrayCollection();
        $this->departments = new ArrayCollection();
        $this->jobTitles = new ArrayCollection();
        $this->cityRegistrations = new ArrayCollection();
        $this->cityCityUsers = new ArrayCollection();
        $this->blockedResumes = new ArrayCollection();
        $this->divisions = new ArrayCollection();
        $this->savedCities = new ArrayCollection();
        $this->createdJobTitleNames = new ArrayCollection();
    }

    /* Start Custom Methods */

    public function getCityAndState()
    {
        return $this->getName().', '.$this->getState();
    }

    public function getPhpTimezone() {
        return timezone_name_from_abbr($this->getTimezone() ?? 'UTC');
    }

    public function getSubscriptionId() {
        $subscription = $this->getSubscription();
        if ($subscription) {
            return $subscription->getPaymentProcessorSubscriptionId();
        }
        return '';
    }

    /**
     * Needed for City subscription cancellation.
     *
     * @param JobAnnouncementStatus $jobAnnouncementStatus
     * @return $this
     */
    public function setAllJobAnnouncementsToStatus(JobAnnouncementStatus $jobAnnouncementStatus)
    {
        foreach ($this->getJobTitles() as $jobTitle) {
            foreach ($jobTitle->getJobAnnouncements() as $ja) {
                $ja->setStatus($jobAnnouncementStatus);
            }
        }
        return $this;
    }

    public function removeAllJobAnnouncements()
    {
        foreach ($this->getJobTitles() as $jobTitle) {
            $jobTitle->removeAllJobAnnouncements();
        }
        return $this;
    }

    /**
     * @ORM\PostLoad()
     * @param LifecycleEventArgs $event
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function calculateUsersWhoSubmittedInterest(LifecycleEventArgs $event)
    {
        /** @var CityRepository $repo */
        $repo = $event->getEntityManager()->getRepository(get_class($this));
        $this->countOfUsersWhoSubmittedInterest = $repo->getCountOfUsersWhoSubmittedInterest($this);
    }

    public function getCountOfUsersWhoSubmittedInterest()
    {
        $users = [];
        foreach ($this->getJobTitles() as $jobTitle) {
            foreach ($jobTitle->getSubmittedJobTitleInterests() as $submittedJobTitleInterest) {
                $users[$submittedJobTitleInterest->getJobSeekerUser()->getId()] = true;
            }
        }
        return count($users);
    }

    public function getPercentageJobTitlesVisible()
    {
        $totalCount = $this->getJobTitles()->count();
        $hiddenCount = $this->getCountHiddenJobTitles();

        return (($totalCount - $hiddenCount) / $totalCount);
    }

    public function getCountAllJobTitles() {
        return $this->getJobTitles()->count();
    }

    public function getCountActiveJobTitles() {
        $expr = Criteria::expr()->eq('isHidden', false);
        $criteria = Criteria::create()
            ->where($expr);
        return $this->getJobTitles()->matching($criteria)->count();
    }

    public function getWouldBePercentageJobTitlesVisible(int $add)
    {
        $totalCount = $this->getJobTitles()->count();
        $hiddenCount = $this->getCountHiddenJobTitles() + $add;

        return $totalCount > 0 ? (($totalCount - $hiddenCount) / $totalCount) : 100;
    }

    public function getCountHiddenJobTitles()
    {
        $expr = Criteria::expr()->eq('isHidden', true);
        $criteria = Criteria::create()
            ->where($expr);
        return $this->getJobTitles()->matching($criteria)->count();
    }

    public function getBannerImageFile()
    {
        return $this->bannerImageFile;
    }

    public function getSealImageFile()
    {
        return $this->sealImageFile;
    }

    public function getState() {
        $counties = $this->getCounties();
        if (count($counties) <= 0) {
            return null;
        }
        return $counties[0]->getState();
    }

    public function getPercentageJobTitlesActive()
    {
        $hiddenCriteria = Criteria::create()->andWhere(Criteria::expr()->eq('isHidden',true));
        $hiddenCount = $this->getJobTitles()->matching($hiddenCriteria)->count();
        $totalCount = $this->getJobTitles()->count();

        if ($totalCount != 0) {
            return ($totalCount - $hiddenCount) / $totalCount;
        }
        return 0;
    }

    public function orderedUrls() {
        $result = [];
        foreach ($this->getUrls() as $url) {
            if ($url->getType()->getId() == URL::JOBSEEKER_DEFAULT_TYPE) {
                array_unshift($result, $url);
            } else {
                $result[] = $url;
            }
        }
        return $result;
    }

    public function __toString()
    {
        return (string) $this->getName();
    }

    public function getDisplayName(City\County $county) {
        return (string)implode(", ", [$this->__toString(),$county->__toString(), $county->getState()->__toString()]);
    }

    public function getMediumName() {
        $counties = $this->getCounties();
        if (count($counties) <= 0) {
            return '';
        }
        $county = $counties[0];
        $state = $county->getState();
        return (string)implode(", ", [$this->__toString(), $state->__toString()]);
    }


    public function getLongName() {
        $counties = $this->getCounties();
        if (count($counties) <= 0) {
            return '';
        }
        $county = $counties[0];
        $state = $county->getState();
        return (string)implode(", ", [$this->__toString(),$county->__toString(), $state->__toString()]);
    }

    public function isPasscodeValid($passcode) {
        return $this->getPasscode() == $passcode;
    }

    public function hasPendingRegistration()
    {
        return $this->getCityRegistrations()->filter(function (CityRegistration $cityRegistration) {
            if ($cityRegistration->getStatus()) {
                return $cityRegistration->getStatus()->getSlug() == CityRegistrationStatus::PENDING_STATUS;
            }
        })->count();
    }

    public function setInverseSide() {
        foreach ($this->getCounties() as $county) {
            $county->addCity($this);
        }

        foreach ($this->getCensusPopulations() as $censusPopulation) {
            $censusPopulation->setCity($this);
        }

        foreach ($this->getOperationHours() as $operationalHour) {
            $operationalHour->setCity($this);
        }

        foreach($this->getJobTitles() as $jobTitle) {
            $jobTitle->setCity($this);
        }

        foreach($this->getDepartments() as $department) {
            $department->setCity($this);
        }

        foreach($this->getUrls() as $url) {
            $url->setCity($this);
        }

        foreach($this->getDivisions() as $division) {
            $division->setCity($this);
        }

    }

    public function setSealImageFile(File $image = null)
    {
        $this->sealImageFile = $image;
        if ($image instanceof UploadedFile || $image instanceof File) {
            $this->updatedAt = new \DateTime('now');
        }
        return $this;
    }

    public function setBannerImageFile(File $image = null)
    {
        $this->bannerImageFile = $image;
        if ($image instanceof UploadedFile) {
            $this->updatedAt = new \DateTime('now');
        }
        return $this;
    }

    public function getStateFromCounty() {
        $counties = $this->getCounties();
        foreach($counties as $county){
            return $county->getState();
        }
        return null;
    }

    public function getFirstCounty() {
        $counties = $this->getCounties();
        foreach($counties as $county){
            return $county;
        }
        return null;
    }

    public function getUrlLastTestedDate() {
        $urls = $this->getUrls();
        $lastTestDate = '';
        foreach ($urls as $index => $url){
            $testDate = $url->getLastTestedDate();
            if($index == 0) {
                $lastTestDate = $testDate;
            }
            if($testDate > $lastTestDate) {
                $lastTestDate = $testDate;
            }
        }
        $lastTestDateFormated = '';
        if ($lastTestDate instanceof \DateTime) {
            $lastTestDateFormated = $lastTestDate->format('m/d/Y');
//            $lastTestDateFormated = $lastTestDate->format('m/d/Y, g:i a');
        }
        return (string) $lastTestDateFormated;
    }

    /* End Custom Methods */

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

    public function getProfileTitle(): ?string
    {
        return $this->profileTitle;
    }

    public function setProfileTitle(?string $profileTitle): self
    {
        $this->profileTitle = $profileTitle;

        return $this;
    }

    public function getProfileAbout(): ?string
    {
        return $this->profileAbout;
    }

    public function setProfileAbout(?string $profileAbout): self
    {
        $this->profileAbout = $profileAbout;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getPrefix(): ?string
    {
        return $this->prefix;
    }

    public function setPrefix(?string $prefix): self
    {
        $this->prefix = $prefix;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getZipCode(): ?int
    {
        return $this->zipCode;
    }

    public function setZipCode(?int $zipCode): self
    {
        $this->zipCode = $zipCode;

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

    public function getCityHallPhone(): ?string
    {
        return $this->cityHallPhone;
    }

    public function setCityHallPhone(?string $cityHallPhone): self
    {
        $this->cityHallPhone = $cityHallPhone;

        return $this;
    }

    public function getTimezone(): ?string
    {
        return $this->timezone;
    }

    public function setTimezone(?string $timezone): self
    {
        $this->timezone = $timezone;

        return $this;
    }

    public function getTimezoneSummer(): ?string
    {
        return $this->timezoneSummer;
    }

    public function setTimezoneSummer(?string $timezoneSummer): self
    {
        $this->timezoneSummer = $timezoneSummer;

        return $this;
    }

    public function getHoursDescription(): ?string
    {
        return $this->hoursDescription;
    }

    public function setHoursDescription(?string $hoursDescription): self
    {
        $this->hoursDescription = $hoursDescription;

        return $this;
    }

    public function getHoursDescriptionOther(): ?string
    {
        return $this->hoursDescriptionOther;
    }

    public function setHoursDescriptionOther(?string $hoursDescriptionOther): self
    {
        $this->hoursDescriptionOther = $hoursDescriptionOther;

        return $this;
    }

    public function getYearFounded(): ?int
    {
        return $this->yearFounded;
    }

    public function setYearFounded(?int $yearFounded): self
    {
        $this->yearFounded = $yearFounded;

        return $this;
    }

    public function getYearChartered(): ?int
    {
        return $this->yearChartered;
    }

    public function setYearChartered(?int $yearChartered): self
    {
        $this->yearChartered = $yearChartered;

        return $this;
    }

    public function getYearIncorporated(): ?int
    {
        return $this->yearIncorporated;
    }

    public function setYearIncorporated(?int $yearIncorporated): self
    {
        $this->yearIncorporated = $yearIncorporated;

        return $this;
    }

    public function getSquareMiles()
    {
        return $this->squareMiles;
    }

    public function setSquareMiles($squareMiles): self
    {
        $this->squareMiles = $squareMiles;

        return $this;
    }

    public function getCountFTE(): ?int
    {
        return $this->countFTE ?? 0;
    }

    public function setCountFTE(?int $countFTE): self
    {
        $this->countFTE = $countFTE;

        return $this;
    }

    public function getHrDirectorFirstName(): ?string
    {
        return $this->hrDirectorFirstName;
    }

    public function setHrDirectorFirstName(?string $hrDirectorFirstName): self
    {
        $this->hrDirectorFirstName = $hrDirectorFirstName;

        return $this;
    }

    public function getHrDirectorLastName(): ?string
    {
        return $this->hrDirectorLastName;
    }

    public function setHrDirectorLastName(?string $hrDirectorLastName): self
    {
        $this->hrDirectorLastName = $hrDirectorLastName;

        return $this;
    }

    public function getHrNamePrefix(): ?string
    {
        return $this->hrNamePrefix;
    }

    public function setHrNamePrefix(?string $hrNamePrefix): self
    {
        $this->hrNamePrefix = $hrNamePrefix;

        return $this;
    }

    public function getHrNameSuffix(): ?string
    {
        return $this->hrNameSuffix;
    }

    public function setHrNameSuffix(?string $hrNameSuffix): self
    {
        $this->hrNameSuffix = $hrNameSuffix;

        return $this;
    }

    public function getHrDirectorTitle(): ?string
    {
        return $this->hrDirectorTitle;
    }

    public function setHrDirectorTitle(?string $hrDirectorTitle): self
    {
        $this->hrDirectorTitle = $hrDirectorTitle;

        return $this;
    }

    public function getHrDirectorPhone(): ?string
    {
        return $this->hrDirectorPhone;
    }

    public function setHrDirectorPhone(?string $hrDirectorPhone): self
    {
        $this->hrDirectorPhone = $hrDirectorPhone;

        return $this;
    }

    public function getHrDirectorEmail(): ?string
    {
        return $this->hrDirectorEmail;
    }

    public function setHrDirectorEmail(?string $hrDirectorEmail): self
    {
        $this->hrDirectorEmail = $hrDirectorEmail;

        return $this;
    }

    public function getMainWebsite(): ?string
    {
        return $this->mainWebsite;
    }

    public function setMainWebsite(?string $mainWebsite): self
    {
        $this->mainWebsite = $mainWebsite;

        return $this;
    }

    public function getSealImage(): ?string
    {
        return $this->sealImage;
    }

    public function setSealImage(?string $sealImage): self
    {
        $this->sealImage = $sealImage;

        return $this;
    }

    public function getBannerImage(): ?string
    {
        return $this->bannerImage;
    }

    public function setBannerImage(?string $bannerImage): self
    {
        $this->bannerImage = $bannerImage;

        return $this;
    }

    public function getIsRegistered(): ?bool
    {
        return $this->isRegistered;
    }

    public function setIsRegistered(bool $isRegistered): self
    {
        $this->isRegistered = $isRegistered;

        return $this;
    }

    public function getAllowsJobAnnouncements(): ?bool
    {
        return $this->allowsJobAnnouncements;
    }

    public function setAllowsJobAnnouncements(bool $allowsJobAnnouncements): self
    {
        $this->allowsJobAnnouncements = $allowsJobAnnouncements;

        return $this;
    }

    public function getIsValidated(): ?bool
    {
        return $this->isValidated;
    }

    public function setIsValidated(bool $isValidated): self
    {
        $this->isValidated = $isValidated;

        return $this;
    }

    public function getDoesCityAllowChanges(): ?bool
    {
        return $this->doesCityAllowChanges;
    }

    public function setDoesCityAllowChanges(bool $doesCityAllowChanges): self
    {
        $this->doesCityAllowChanges = $doesCityAllowChanges;

        return $this;
    }

    public function getProfileAddedDate(): ?\DateTimeInterface
    {
        return $this->profileAddedDate;
    }

    public function setProfileAddedDate(?\DateTimeInterface $profileAddedDate): self
    {
        $this->profileAddedDate = $profileAddedDate;

        return $this;
    }

    public function getJobTitlesAddedDate(): ?\DateTimeInterface
    {
        return $this->jobTitlesAddedDate;
    }

    public function setJobTitlesAddedDate(?\DateTimeInterface $jobTitlesAddedDate): self
    {
        $this->jobTitlesAddedDate = $jobTitlesAddedDate;

        return $this;
    }

    public function getCountJobTitles(): ?int
    {
        return $this->countJobTitles;
    }

    public function setCountJobTitles(?int $countJobTitles): self
    {
        $this->countJobTitles = $countJobTitles;

        return $this;
    }

    /**
     * @return Collection|County[]
     */
    public function getCounties(): Collection
    {
        return $this->counties;
    }

    public function addCounty(County $county): self
    {
        if (!$this->counties->contains($county)) {
            $this->counties[] = $county;
            $county->addCity($this);
        }

        return $this;
    }

    public function removeCounty(County $county): self
    {
        if ($this->counties->contains($county)) {
            $this->counties->removeElement($county);
            $county->removeCity($this);
        }

        return $this;
    }

    public function getProfileType(): ?ProfileType
    {
        return $this->profileType;
    }

    public function setProfileType(?ProfileType $profileType): self
    {
        $this->profileType = $profileType;

        return $this;
    }

    /**
     * @return Collection|Url[]
     */
    public function getUrls(): Collection
    {
        return $this->urls;
    }

    public function addUrl(Url $url): self
    {
        if (!$this->urls->contains($url)) {
            $this->urls[] = $url;
            $url->setCity($this);
        }

        return $this;
    }

    public function removeUrl(Url $url): self
    {
        if ($this->urls->contains($url)) {
            $this->urls->removeElement($url);
            // set the owning side to null (unless already changed)
            if ($url->getCity() === $this) {
                $url->setCity(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|CensusPopulation[]
     */
    public function getCensusPopulations(): Collection
    {
        return $this->censusPopulations;
    }

    public function addCensusPopulation(CensusPopulation $censusPopulation): self
    {
        if (!$this->censusPopulations->contains($censusPopulation)) {
            $this->censusPopulations[] = $censusPopulation;
            $censusPopulation->setCity($this);
        }

        return $this;
    }

    public function removeCensusPopulation(CensusPopulation $censusPopulation): self
    {
        if ($this->censusPopulations->contains($censusPopulation)) {
            $this->censusPopulations->removeElement($censusPopulation);
            // set the owning side to null (unless already changed)
            if ($censusPopulation->getCity() === $this) {
                $censusPopulation->setCity(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|OperationHours[]
     */
    public function getOperationHours(): Collection
    {
        return $this->operationHours;
    }

    public function addOperationHour(OperationHours $operationHour): self
    {
        if (!$this->operationHours->contains($operationHour)) {
            $this->operationHours[] = $operationHour;
            $operationHour->setCity($this);
        }

        return $this;
    }

    public function removeOperationHour(OperationHours $operationHour): self
    {
        if ($this->operationHours->contains($operationHour)) {
            $this->operationHours->removeElement($operationHour);
            // set the owning side to null (unless already changed)
            if ($operationHour->getCity() === $this) {
                $operationHour->setCity(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Department[]
     */
    public function getDepartments(): Collection
    {
        return $this->departments;
    }

    public function addDepartment(Department $department): self
    {
        if (!$this->departments->contains($department)) {
            $this->departments[] = $department;
            $department->setCity($this);
        }

        return $this;
    }

    public function removeDepartment(Department $department): self
    {
        if ($this->departments->contains($department)) {
            $this->departments->removeElement($department);
            // set the owning side to null (unless already changed)
            if ($department->getCity() === $this) {
                $department->setCity(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|JobTitle[]
     */
    public function getJobTitles(): Collection
    {
        return $this->jobTitles;
    }

    public function addJobTitle(JobTitle $jobTitle): self
    {
        if (!$this->jobTitles->contains($jobTitle)) {
            $this->jobTitles[] = $jobTitle;
            $jobTitle->setCity($this);
        }

        return $this;
    }

    public function removeJobTitle(JobTitle $jobTitle): self
    {
        if ($this->jobTitles->contains($jobTitle)) {
            $this->jobTitles->removeElement($jobTitle);
            // set the owning side to null (unless already changed)
            if ($jobTitle->getCity() === $this) {
                $jobTitle->setCity(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|CityRegistration[]
     */
    public function getCityRegistrations(): Collection
    {
        return $this->cityRegistrations;
    }

    public function addCityRegistration(CityRegistration $cityRegistration): self
    {
        if (!$this->cityRegistrations->contains($cityRegistration)) {
            $this->cityRegistrations[] = $cityRegistration;
            $cityRegistration->setCity($this);
        }

        return $this;
    }

    public function removeCityRegistration(CityRegistration $cityRegistration): self
    {
        if ($this->cityRegistrations->contains($cityRegistration)) {
            $this->cityRegistrations->removeElement($cityRegistration);
            // set the owning side to null (unless already changed)
            if ($cityRegistration->getCity() === $this) {
                $cityRegistration->setCity(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|CityCityUser[]
     */
    public function getCityCityUsers(): Collection
    {
        return $this->cityCityUsers;
    }

    public function addCityCityUser(CityCityUser $cityCityUser): self
    {
        if (!$this->cityCityUsers->contains($cityCityUser)) {
            $this->cityCityUsers[] = $cityCityUser;
            $cityCityUser->setCity($this);
        }

        return $this;
    }

    public function removeCityCityUser(CityCityUser $cityCityUser): self
    {
        if ($this->cityCityUsers->contains($cityCityUser)) {
            $this->cityCityUsers->removeElement($cityCityUser);
            // set the owning side to null (unless already changed)
            if ($cityCityUser->getCity() === $this) {
                $cityCityUser->setCity(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Resume[]
     */
    public function getBlockedResumes(): Collection
    {
        return $this->blockedResumes;
    }

    public function addBlockedResume(Resume $blockedResume): self
    {
        if (!$this->blockedResumes->contains($blockedResume)) {
            $this->blockedResumes[] = $blockedResume;
            $blockedResume->addCitiesToBlock($this);
        }

        return $this;
    }

    public function removeBlockedResume(Resume $blockedResume): self
    {
        if ($this->blockedResumes->contains($blockedResume)) {
            $this->blockedResumes->removeElement($blockedResume);
            $blockedResume->removeCitiesToBlock($this);
        }

        return $this;
    }

    /**
     * @return Collection|Division[]
     */
    public function getDivisions(): Collection
    {
        return $this->divisions;
    }

    public function addDivision(Division $division): self
    {
        if (!$this->divisions->contains($division)) {
            $this->divisions[] = $division;
            $division->setCity($this);
        }

        return $this;
    }

    public function removeDivision(Division $division): self
    {
        if ($this->divisions->contains($division)) {
            $this->divisions->removeElement($division);
            // set the owning side to null (unless already changed)
            if ($division->getCity() === $this) {
                $division->setCity(null);
            }
        }

        return $this;
    }

    public function getSubscription(): ?Subscription
    {
        return $this->subscription;
    }

    public function setSubscription(?Subscription $subscription): self
    {
        $this->subscription = $subscription;

        // set (or unset) the owning side of the relation if necessary
        $newCity = $subscription === null ? null : $this;
        if ($newCity !== $subscription->getCity()) {
            $subscription->setCity($newCity);
        }

        return $this;
    }

    public function getAdminCityUser(): ?CityUser
    {
        return $this->adminCityUser;
    }

    public function setAdminCityUser(?CityUser $adminCityUser): self
    {
        $this->adminCityUser = $adminCityUser;

        return $this;
    }

    public function getIsSuspended(): ?bool
    {
        return $this->isSuspended;
    }

    public function setIsSuspended(bool $isSuspended): self
    {
        $this->isSuspended = $isSuspended;

        return $this;
    }

    public function getSuspensionEmailSentAt(): ?\DateTimeInterface
    {
        return $this->suspensionEmailSentAt;
    }

    public function setSuspensionEmailSentAt(?\DateTimeInterface $suspensionEmailSentAt): self
    {
        $this->suspensionEmailSentAt = $suspensionEmailSentAt;

        return $this;
    }

    /**
     * @return Collection|SavedCity[]
     */
    public function getSavedCities(): Collection
    {
        return $this->savedCities;
    }

    public function addSavedCity(SavedCity $savedCity): self
    {
        if (!$this->savedCities->contains($savedCity)) {
            $this->savedCities[] = $savedCity;
            $savedCity->setCity($this);
        }

        return $this;
    }

    public function removeSavedCity(SavedCity $savedCity): self
    {
        if ($this->savedCities->contains($savedCity)) {
            $this->savedCities->removeElement($savedCity);
            // set the owning side to null (unless already changed)
            if ($savedCity->getCity() === $this) {
                $savedCity->setCity(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|JobTitleName[]
     */
    public function getCreatedJobTitleNames(): Collection
    {
        return $this->createdJobTitleNames;
    }

    public function addCreatedJobTitleName(JobTitleName $createdJobTitleName): self
    {
        if (!$this->createdJobTitleNames->contains($createdJobTitleName)) {
            $this->createdJobTitleNames[] = $createdJobTitleName;
            $createdJobTitleName->setCreatedByCity($this);
        }

        return $this;
    }

    public function removeCreatedJobTitleName(JobTitleName $createdJobTitleName): self
    {
        if ($this->createdJobTitleNames->contains($createdJobTitleName)) {
            $this->createdJobTitleNames->removeElement($createdJobTitleName);
            // set the owning side to null (unless already changed)
            if ($createdJobTitleName->getCreatedByCity() === $this) {
                $createdJobTitleName->setCreatedByCity(null);
            }
        }

        return $this;
    }

    public function setTempState(State $state = null)
    {
        $this->tempState = $state;

        return $this;
    }

    public function getTempState(): ?State
    {
        return $this->tempState;
    }

    public function getCurrentStars(): ?int
    {
        return $this->currentStars;
    }

    public function setCurrentStars(int $currentStars): self
    {
        $this->currentStars = $currentStars;

        return $this;
    }

    public function getCgjPostsJobs(): ?bool
    {
        return $this->cgjPostsJobs;
    }

    public function setCgjPostsJobs(?bool $cgjPostsJobs): self
    {
        $this->cgjPostsJobs = $cgjPostsJobs;

        return $this;
    }
}

