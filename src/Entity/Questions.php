<?php

namespace App\Entity;

use App\Repository\QuestionsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=QuestionsRepository::class)
 */
class Questions
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
    private $question_text;

    /**
     * @ORM\Column(type="boolean")
     */
    private $active;

    /**
     * @ORM\Column(type="string", length=10)
     */
    private $question_type;

    /**
     * @ORM\ManyToOne(targetEntity=Steps::class, inversedBy="questions")
     */
    private $step;

    /**
     * @ORM\OneToMany(targetEntity=Suggestions::class, mappedBy="question")
     */
    private $suggestions;

    /**
     * @ORM\OneToMany(targetEntity=Suggestions::class, mappedBy="question")
     */
    private $question;

    public function __construct()
    {
        $this->suggestions = new ArrayCollection();
        $this->question = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuestionText(): ?string
    {
        return $this->question_text;
    }

    public function setQuestionText(string $question_text): self
    {
        $this->question_text = $question_text;

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

    public function getQuestionType(): ?string
    {
        return $this->question_type;
    }

    public function setQuestionType(string $question_type): self
    {
        $this->question_type = $question_type;

        return $this;
    }

    public function getStep(): ?Steps
    {
        return $this->step;
    }

    public function setStep(?Steps $step): self
    {
        $this->step = $step;

        return $this;
    }

    /**
     * @return Collection|Suggestions[]
     */
    public function getSuggestions(): Collection
    {
        return $this->suggestions;
    }

    public function addSuggestion(Suggestions $suggestion): self
    {
        if (!$this->suggestions->contains($suggestion)) {
            $this->suggestions[] = $suggestion;
            $suggestion->setQuestion($this);
        }

        return $this;
    }

    public function removeSuggestion(Suggestions $suggestion): self
    {
        if ($this->suggestions->contains($suggestion)) {
            $this->suggestions->removeElement($suggestion);
            // set the owning side to null (unless already changed)
            if ($suggestion->getQuestion() === $this) {
                $suggestion->setQuestion(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Suggestions[]
     */
    public function getQuestion(): Collection
    {
        return $this->question;
    }

    public function addQuestion(Suggestions $question): self
    {
        if (!$this->question->contains($question)) {
            $this->question[] = $question;
            $question->setQuestion($this);
        }

        return $this;
    }

    public function removeQuestion(Suggestions $question): self
    {
        if ($this->question->contains($question)) {
            $this->question->removeElement($question);
            // set the owning side to null (unless already changed)
            if ($question->getQuestion() === $this) {
                $question->setQuestion(null);
            }
        }

        return $this;
    }
}
