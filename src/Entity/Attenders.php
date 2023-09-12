<?php

namespace App\Entity;

use App\Repository\AttendersRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AttendersRepository::class)
 */
class Attenders
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $nname;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $email;

    /**
     * @ORM\Column(type="date")
     */
    private $attend_date;

    /**
     * @ORM\OneToMany(targetEntity=Answers::class, mappedBy="attender")
     */
    private $answer;

    public function __construct()
    {
        $this->answer = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNname(): ?string
    {
        return $this->nname;
    }

    public function setNname(string $nname): self
    {
        $this->nname = $nname;

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

    public function getAttendDate(): ?\DateTimeInterface
    {
        return $this->attend_date;
    }

    public function setAttendDate(\DateTimeInterface $attend_date): self
    {
        $this->attend_date = $attend_date;

        return $this;
    }

    /**
     * @return Collection|Answers[]
     */
    public function getAnswer(): Collection
    {
        return $this->answer;
    }

    public function addAnswer(Answers $answer): self
    {
        if (!$this->answer->contains($answer)) {
            $this->answer[] = $answer;
            $answer->setAttender($this);
        }

        return $this;
    }

    public function removeAnswer(Answers $answer): self
    {
        if ($this->answer->contains($answer)) {
            $this->answer->removeElement($answer);
            // set the owning side to null (unless already changed)
            if ($answer->getAttender() === $this) {
                $answer->setAttender(null);
            }
        }

        return $this;
    }
}
