<?php

namespace App\Entity\City\Importer;

use Doctrine\ORM\Mapping as ORM;
use App\Traits\BlameableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @ORM\Entity(repositoryClass="App\Repository\City\Uploader\JobTitleUploadRepository")
 */
class JobTitleUpload
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
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    private $fileName;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $result;

    /**
     * @var UploadedFile
     *
     * @ORM\Column(type="string", length=255)
     */
    private $file;

    public function __toString()
    {
        return (string) $this->getFileName();
    }

    /**
     * Upload attachment file
     */
    public function convertUploadedCsvToArray()
    {
        if (null === $this->getFile()) {
            return;
        }

        $this->getFile()->move('upload/csv', $this->getFile()->getClientOriginalName());

        $csvFile = file('upload/csv/'.$this->getFile()->getClientOriginalName());

        $csvDefaults = array_map('str_getcsv', $csvFile);

        return $csvDefaults;
    }

    public function removeUploadedFile(){
        if (null === $this->getFile()) {
            return;
        }

        unlink('upload/csv/'.$this->getFile()->getClientOriginalName());
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets file.
     *
     * @param UploadedFile $file
     */
    public function setFile(UploadedFile $file = null)
    {
        $this->file = $file;
    }

    /**
     * Get file.
     *
     * @return UploadedFile
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Get fileName.
     *
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * Set fileName.
     *
     * @param string $fileName
     *
     * @return JobTitleUpload
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;

        return $this;
    }

    public function getResult(): ?string
    {
        return $this->result;
    }

    public function setResult(?string $result): self
    {
        $this->result = $result;

        return $this;
    }
}
