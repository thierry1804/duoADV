<?php

namespace App\Entity;

use App\Repository\SaleRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SaleRepository::class)]
class Sale
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'sales')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $registeredBy = null;

    #[ORM\ManyToOne(inversedBy: 'sales')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Article $item = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $recordedAt = null;

    #[ORM\Column]
    private ?float $qty = null;

    #[ORM\Column(nullable: true)]
    private ?float $qtyReturned = null;

    #[ORM\Column(nullable: true)]
    private ?bool $received = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $soldOn = null;

    #[ORM\Column(nullable: true)]
    private ?bool $historized = null;

    public function __construct()
    {
        $this->recordedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRegisteredBy(): ?User
    {
        return $this->registeredBy;
    }

    public function setRegisteredBy(?User $registeredBy): static
    {
        $this->registeredBy = $registeredBy;

        return $this;
    }

    public function getItem(): ?Article
    {
        return $this->item;
    }

    public function setItem(?Article $item): static
    {
        $this->item = $item;

        return $this;
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

    public function getQty(): ?float
    {
        return $this->qty;
    }

    public function setQty(float $qty): static
    {
        $this->qty = $qty;

        return $this;
    }

    public function getQtyReturned(): ?float
    {
        return $this->qtyReturned;
    }

    public function setQtyReturned(?float $qtyReturned): static
    {
        $this->qtyReturned = $qtyReturned;

        return $this;
    }

    public function isReceived(): ?bool
    {
        return $this->received;
    }

    public function setReceived(?bool $received): static
    {
        $this->received = $received;

        return $this;
    }

    public function getSoldOn(): ?\DateTimeInterface
    {
        return $this->soldOn;
    }

    public function setSoldOn(\DateTimeInterface $soldOn): static
    {
        $this->soldOn = $soldOn;

        return $this;
    }

    public function isHistorized(): ?bool
    {
        return $this->historized;
    }

    public function setHistorized(?bool $historized): static
    {
        $this->historized = $historized;

        return $this;
    }
}
