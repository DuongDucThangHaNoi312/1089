<?php

namespace App\Entity\City;

use App\Entity\City;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\City\DepartmentRepository")
 * @UniqueEntity(
 *     fields = {"city", "name"},
 *     errorPath = "",
 *     message = "This department already exists in this city."
 * )
 */
class Department
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @var string
     * @Gedmo\Slug(fields={"name"}, updatable=true)
     * @ORM\Column(type="string", length=255)
     */
    private $slug;

    /**
     * @var \App\Entity\City
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\City", inversedBy="departments")
     * @ORM\JoinColumn(name="city_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $city;

    /**
     *
     * @ORM\OneToMany(targetEntity="App\Entity\City\JobTitle", mappedBy="department", cascade={"persist", "remove"})
     */
    private $jobTitles;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\City\Division", mappedBy="department", orphanRemoval=true)
     */
    private $divisions;

    /**
     * @ORM\Column(type="boolean")
     */
    private $hideOnProfilePage = false;

    /**
     * @ORM\Column(type="integer")
     */
    private $orderByNumber;

    public function __construct()
    {
        $this->jobTitles = new ArrayCollection();
        $this->divisions = new ArrayCollection();
    }

    public function __toString()
    {
        return (string) $this->getName();
    }

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

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getCity(): ?\App\Entity\City
    {
        return $this->city;
    }

    public function setCity(?\App\Entity\City $city): self
    {
        $this->city = $city;

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
            $jobTitle->setDepartment($this);
        }

        return $this;
    }

    public function removeJobTitle(JobTitle $jobTitle): self
    {
        if ($this->jobTitles->contains($jobTitle)) {
            $this->jobTitles->removeElement($jobTitle);
            // set the owning side to null (unless already changed)
            if ($jobTitle->getDepartment() === $this) {
                $jobTitle->setDepartment(null);
            }
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
            $division->setDepartment($this);
        }

        return $this;
    }

    public function removeDivision(Division $division): self
    {
        if ($this->divisions->contains($division)) {
            $this->divisions->removeElement($division);
            // set the owning side to null (unless already changed)
            if ($division->getDepartment() === $this) {
                $division->setDepartment(null);
            }
        }

        return $this;
    }

    public function getHideOnProfilePage(): ?bool
    {
        return $this->hideOnProfilePage;
    }

    public function setHideOnProfilePage(bool $hideOnProfilePage): self
    {
        $this->hideOnProfilePage = $hideOnProfilePage;

        return $this;
    }

    public function getOrderByNumber(): ?int
    {
        return $this->orderByNumber;
    }

    public function setOrderByNumber(int $orderByNumber): self
    {
        $this->orderByNumber = $orderByNumber;

        return $this;
    }

 
}
