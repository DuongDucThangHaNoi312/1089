<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CMSJobCategoryRepository")
 * @Vich\Uploadable
 */
class CMSJobCategory
{
    use TimestampableEntity;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $gridImage;

    /**
     * @Vich\UploadableField(mapping="city_job_categories_images", fileNameProperty="gridImage")
     * @var File
     */
    private $gridImageFile;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $detailImage;

    /**
     * @Vich\UploadableField(mapping="city_job_categories_detail_images", fileNameProperty="detailImage")
     * @var File
     */
    private $detailImageFile;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGridImageFile()
    {
        return $this->gridImageFile;
    }

    public function setGridImageFile(File $image = null)
    {
        $this->gridImageFile = $image;
        if ($image instanceof UploadedFile) {
            $this->updatedAt = new \DateTime('now');
        }
        return $this;
    }

    public function getDetailImageFile()
    {
        return $this->detailImageFile;
    }

    public function setDetailImageFile(File $detailImageFile)
    {
        $this->detailImageFile = $detailImageFile;
        if ($detailImageFile instanceof UploadedFile) {
            $this->updatedAt = new \DateTime('now');
        }
        return $this;
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

    public function getGridImage(): ?string
    {
        return $this->gridImage;
    }

    public function setGridImage(?string $gridImage): self
    {
        $this->gridImage = $gridImage;

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

    public function getDetailImage(): ?string
    {
        return $this->detailImage;
    }

    public function setDetailImage(?string $detailImage): self
    {
        $this->detailImage = $detailImage;

        return $this;
    }

}
