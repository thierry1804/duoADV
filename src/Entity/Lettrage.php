<?php

namespace App\Entity;

use App\Repository\LettrageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LettrageRepository::class)]
class Lettrage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $recordedAt = null;

    #[ORM\ManyToOne(inversedBy: 'lettrages')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $recordedBy = null;

    #[ORM\Column(length: 255)]
    private ?string $label = null;

    /**
     * @var Collection<int, Sale>
     */
    #[ORM\OneToMany(targetEntity: Sale::class, mappedBy: 'lettrage')]
    private Collection $sales;

    /**
     * @var Collection<int, Expense>
     */
    #[ORM\OneToMany(targetEntity: Expense::class, mappedBy: 'lettrage')]
    private Collection $expenses;

    public function __construct()
    {
        $this->sales = new ArrayCollection();
        $this->expenses = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRecordedAt(): ?\DateTimeImmutable
    {
        return $this->recordedAt;
    }

    public function setRecordedAt(\DateTimeImmutable $recordedAt): static
    {
        $this->recordedAt = $recordedAt;

        return $this;
    }

    public function getRecordedBy(): ?User
    {
        return $this->recordedBy;
    }

    public function setRecordedBy(?User $recordedBy): static
    {
        $this->recordedBy = $recordedBy;

        return $this;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @return Collection<int, Sale>
     */
    public function getSales(): Collection
    {
        return $this->sales;
    }

    public function addSale(Sale $sale): static
    {
        if (!$this->sales->contains($sale)) {
            $this->sales->add($sale);
            $sale->setLettrage($this);
        }

        return $this;
    }

    public function removeSale(Sale $sale): static
    {
        if ($this->sales->removeElement($sale)) {
            // set the owning side to null (unless already changed)
            if ($sale->getLettrage() === $this) {
                $sale->setLettrage(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Expense>
     */
    public function getExpenses(): Collection
    {
        return $this->expenses;
    }

    public function addExpense(Expense $expense): static
    {
        if (!$this->expenses->contains($expense)) {
            $this->expenses->add($expense);
            $expense->setLettrage($this);
        }

        return $this;
    }

    public function removeExpense(Expense $expense): static
    {
        if ($this->expenses->removeElement($expense)) {
            // set the owning side to null (unless already changed)
            if ($expense->getLettrage() === $this) {
                $expense->setLettrage(null);
            }
        }

        return $this;
    }
}
