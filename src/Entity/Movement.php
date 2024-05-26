<?php

namespace App\Entity;

use App\Repository\MovementRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MovementRepository::class)]
class Movement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $recordedAt = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $operatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'movements')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Article $article = null;

    #[ORM\Column]
    private ?int $type = null;

    #[ORM\Column]
    private ?int $reference = null;

    #[ORM\Column]
    private ?float $stockBefore = null;

    #[ORM\Column]
    private ?float $qty = null;

    #[ORM\Column]
    private ?float $stockAfter = null;

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

    public function getOperatedAt(): ?\DateTimeInterface
    {
        return $this->operatedAt;
    }

    public function setOperatedAt(\DateTimeInterface $operatedAt): static
    {
        $this->operatedAt = $operatedAt;

        return $this;
    }

    public function getArticle(): ?Article
    {
        return $this->article;
    }

    public function setArticle(?Article $article): static
    {
        $this->article = $article;

        return $this;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(int $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getReference(): ?int
    {
        return $this->reference;
    }

    public function setReference(int $reference): static
    {
        $this->reference = $reference;

        return $this;
    }

    public function getStockBefore(): ?float
    {
        return $this->stockBefore;
    }

    public function setStockBefore(float $stockBefore): static
    {
        $this->stockBefore = $stockBefore;

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

    public function getStockAfter(): ?float
    {
        return $this->stockAfter;
    }

    public function setStockAfter(float $stockAfter): static
    {
        $this->stockAfter = $stockAfter;

        return $this;
    }
}
