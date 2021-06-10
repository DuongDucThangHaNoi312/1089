<?php

namespace App\Entity\User;

use App\Entity\City;
use App\Entity\CityRegistration;
use App\Entity\City\Department;
use App\Entity\City\JobTitle;
use App\Entity\JobAnnouncement;
use App\Entity\User as BaseUser;
use App\Entity\User\CityUser\SavedResume;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\User\CityUserRepository")
 */
class CityUser extends BaseUser
{
    /**
     * @var \App\Entity\City\JobTitle
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\City\JobTitle")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $jobTitle;

    /**
     * @var \App\Entity\City\Department
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\City\Department")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $department;

    /**
     * @var ArrayCollection|CityRegistration[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\CityRegistration", mappedBy="cityUser", cascade={"persist"})
     */
    private $cityRegistrations;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\User\CityUser\SavedResume", mappedBy="cityUser", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $savedResumes;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\JobAnnouncement", mappedBy="assignedTo", orphanRemoval=false)
     */
    private $jobAnnouncements;

    /**
     * @var \App\Entity\City
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\City")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $city;

    public function hasSavedResume(BaseUser\JobSeekerUser\Resume $resume)
    {
        return $this->getSavedResumes()->matching(Criteria::create()->where(Criteria::expr()->eq('resume', $resume)))->count();
    }

    public function __construct()
    {
        parent::__construct();
        $this->cityRegistrations = new ArrayCollection();
        $this->savedResumes = new ArrayCollection();
        $this->jobAnnouncements = new ArrayCollection();
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

    public function getDepartment(): ?Department
    {
        return $this->department;
    }

    public function setDepartment(?Department $department): self
    {
        $this->department = $department;

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
            $cityRegistration->setCityUser($this);
        }

        return $this;
    }

    public function removeCityRegistration(CityRegistration $cityRegistration): self
    {
        if ($this->cityRegistrations->contains($cityRegistration)) {
            $this->cityRegistrations->removeElement($cityRegistration);
            // set the owning side to null (unless already changed)
            if ($cityRegistration->getCityUser() === $this) {
                $cityRegistration->setCityUser(null);
            }
        }

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
            $savedResume->setCityUser($this);
        }

        return $this;
    }

    public function removeSavedResume(SavedResume $savedResume): self
    {
        if ($this->savedResumes->contains($savedResume)) {
            $this->savedResumes->removeElement($savedResume);
            // set the owning side to null (unless already changed)
            if ($savedResume->getCityUser() === $this) {
                $savedResume->setCityUser(null);
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
            $jobAnnouncement->setAssignedTo($this);
        }

        return $this;
    }

    public function removeJobAnnouncement(JobAnnouncement $jobAnnouncement): self
    {
        if ($this->jobAnnouncements->contains($jobAnnouncement)) {
            $this->jobAnnouncements->removeElement($jobAnnouncement);
            // set the owning side to null (unless already changed)
            if ($jobAnnouncement->getAssignedTo() === $this) {
                $jobAnnouncement->setAssignedTo(null);
            }
        }

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

}