<?php

namespace App\Entity\JobTitle\Lookup;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="App\Repository\JobTitle\Lookup\JobLevelRepository")
 */
class JobLevel
{
    CONST JOB_LEVEL_ENTRY = 'entry';
    CONST JOB_LEVEL_MID   = 'mid';

    CONST JOB_LEVEL_SENIOR    = 'senior';
    CONST JOB_LEVEL_EXECUTIVE = 'executive';

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
     * @Gedmo\Slug(fields={"name"}, updatable=true)
     * @ORM\Column(type="string", length=255)
     */
    private $slug;

    /**
     * @Gedmo\SortablePosition
     * @ORM\Column(name="position", type="integer")
     */
    private $position = 0;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $description;

    public function __toString()
    {
        return (string) $this->getName();
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return JobLevel
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get slug.
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set slug.
     *
     * @param string $slug
     *
     * @return JobLevel
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): self
    {
        $this->position = $position;

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

    public function getNameAndDescription() {
        if ($this->getDescription()) {
            return $this->getName(). ' '. $this->getDescription();
        } else {
            return $this->getName();
        }
    }
}
