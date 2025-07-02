<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Image;

class Option
{
    private ?int $id = null;
    private string $name;
    private ?string $description = null;
    private float $price;
    private float $tva;
    private ?Image $image = null;
    private ?bool $perAdult = false;
    private ?bool $perChild = false;
    private ?bool $perNight = false;
    private ?bool $perQuantity = false;
    private ?int $maxQuantity = null;
    private ?string $quantityIdentifier = null;
    private \DateTime $createdAt;
    private \DateTime $updatedAt;

    public function __construct(
        ?int $id,
        string $name,
        ?string $description,
        float $price,
        float $tva,
        ?Image $image,
        ?bool $perAdult,
        ?bool $perChild,
        ?bool $perNight,
        ?bool $perQuantity,
        ?int $maxQuantity,
        ?string $quantityIdentifier,
        \DateTime $createdAt,
        \DateTime $updatedAt,
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->price = $price;
        $this->tva = $tva;
        $this->image = $image;
        $this->perAdult = $perAdult;
        $this->perChild = $perChild;
        $this->perNight = $perNight;
        $this->perQuantity = $perQuantity;
        $this->maxQuantity = $maxQuantity;
        $this->quantityIdentifier = $quantityIdentifier;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function setPrice(float $price): void
    {
        $this->price = $price;
    }

    public function getTva(): float
    {
        return $this->tva;
    }

    public function setTva(float $tva): void
    {
        $this->tva = $tva;
    }

    public function getImage(): ?Image
    {
        return $this->image;
    }
    public function setImage(?Image $image): void
    {
        $this->image = $image;
    }

    public function isPerAdult(): bool
    {
        return $this->perAdult;
    }

    public function setPerAdult(bool $perAdult): void
    {
        $this->perAdult = $perAdult;
    }

    public function isPerChild(): bool
    {
        return $this->perChild;
    }

    public function setPerChild(bool $perChild): void
    {
        $this->perChild = $perChild;
    }

    public function isPerNight(): bool
    {
        return $this->perNight;
    }

    public function setPerNight(bool $perNight): void
    {
        $this->perNight = $perNight;
    }

    public function isPerQuantity(): bool
    {
        return $this->perQuantity;
    }

    public function setPerQuantity(bool $perQuantity): void
    {
        $this->perQuantity = $perQuantity;
    }

    public function getMaxQuantity(): ?int
    {
        return $this->maxQuantity;
    }

    public function setMaxQuantity(?int $maxQuantity): void
    {
        $this->maxQuantity = $maxQuantity;
    }

    public function getQuantityIdentifier(): ?string
    {
        return $this->quantityIdentifier;
    }

    public function setQuantityIdentifier(?string $quantityIdentifier): void
    {
        $this->quantityIdentifier = $quantityIdentifier;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTime $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }
}
