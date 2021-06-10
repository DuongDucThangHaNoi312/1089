<?php

namespace App\Entity\User;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\User\UserLoginRepository")
 */
class UserLogin
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User\JobSeekerUser", inversedBy="userLogins")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $loginTime;

    public function __construct()
    {
        $this->loginTime = new \DateTime('now');
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?JobSeekerUser
    {
        return $this->user;
    }

    public function setUser(?JobSeekerUser $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getLoginTime(): ?\DateTimeInterface
    {
        return $this->loginTime;
    }

    public function setLoginTime(?\DateTimeInterface $loginTime): self
    {
        $this->loginTime = $loginTime;

        return $this;
    }
}
