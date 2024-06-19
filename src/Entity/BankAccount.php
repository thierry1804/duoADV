<?php

namespace App\Entity;

use App\Repository\BankAccountRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BankAccountRepository::class)]
class BankAccount
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $label = null;

    #[ORM\Column(length: 23, nullable: true)]
    private ?string $accountNumber = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $addedAt = null;

    /**
     * @var Collection<int, BankReconciliation>
     */
    #[ORM\OneToMany(targetEntity: BankReconciliation::class, mappedBy: 'bankAccount', orphanRemoval: true)]
    private Collection $bankReconciliations;

    public function __construct()
    {
        $this->bankReconciliations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getAccountNumber(): ?string
    {
        return $this->accountNumber;
    }

    public function setAccountNumber(?string $accountNumber): static
    {
        $this->accountNumber = $accountNumber;

        return $this;
    }

    public function getAddedAt(): ?\DateTimeImmutable
    {
        return $this->addedAt;
    }

    public function setAddedAt(\DateTimeImmutable $addedAt): static
    {
        $this->addedAt = $addedAt;

        return $this;
    }

    /**
     * @return Collection<int, BankReconciliation>
     */
    public function getBankReconciliations(): Collection
    {
        return $this->bankReconciliations;
    }

    public function addBankReconciliation(BankReconciliation $bankReconciliation): static
    {
        if (!$this->bankReconciliations->contains($bankReconciliation)) {
            $this->bankReconciliations->add($bankReconciliation);
            $bankReconciliation->setBankAccount($this);
        }

        return $this;
    }

    public function removeBankReconciliation(BankReconciliation $bankReconciliation): static
    {
        if ($this->bankReconciliations->removeElement($bankReconciliation)) {
            // set the owning side to null (unless already changed)
            if ($bankReconciliation->getBankAccount() === $this) {
                $bankReconciliation->setBankAccount(null);
            }
        }

        return $this;
    }
}
