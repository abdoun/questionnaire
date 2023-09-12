<?php

namespace App\Entity;

use App\Repository\UsersRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=UsersRepository::class)
 */
class Users
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $email;

    /**
     * @ORM\Column(type="boolean")
     */
    private $active;

    /**
     * @ORM\Column(type="string", length=10)
     */
    private $level;

    /**
     * @ORM\OneToMany(targetEntity=Questionnaires::class, mappedBy="user")
     */
    private $questionnaire;



    public function __construct()
    {
        $this->questionnaire = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function getLevel(): ?string
    {
        return $this->level;
    }

    public function setLevel(string $level): self
    {
        $this->level = $level;

        return $this;
    }

    /**
     * @return Collection|Questionnaires[]
     */
    public function getQuestionnaire(): Collection
    {
        return $this->questionnaire;
    }

    public function addQuestionnaire(Questionnaires $questionnaire): self
    {
        if (!$this->questionnaire->contains($questionnaire)) {
            $this->questionnaire[] = $questionnaire;
            $questionnaire->setUser($this);
        }

        return $this;
    }

    public function removeQuestionnaire(Questionnaires $questionnaire): self
    {
        if ($this->questionnaire->contains($questionnaire)) {
            $this->questionnaire->removeElement($questionnaire);
            // set the owning side to null (unless already changed)
            if ($questionnaire->getUser() === $this) {
                $questionnaire->setUser(null);
            }
        }

        return $this;
    }

}
