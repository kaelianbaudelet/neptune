<?php
// src/Entity/Room.php

declare(strict_types=1);

namespace App\Entity;

use InvalidArgumentException;

use App\Entity\Type;

class Room
{
    private ?int $id = null;
    private string $name;
    private string $description;
    private int $bedSingle;
    private int $bedDouble;
    private int $capacity;
    private ?bool $available = null;
    private Type $type;
    private float $price;
    private ?float $discount = 0.0;
    private \DateTime $createdAt;
    private \DateTime $updatedAt;

    public array $images = [];
    public array $equipments = [];

    public function __construct(
        ?int $id,
        string $name,
        string $description,
        int $bedSingle,
        int $bedDouble,
        int $capacity,
        ?bool $available,
        Type $type,
        float $price,
        ?float $discount,
        \DateTime $createdAt,
        \DateTime $updatedAt
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->bedSingle = $bedSingle;
        $this->bedDouble = $bedDouble;
        $this->capacity = $capacity;
        $this->available = $available;
        $this->type = $type;
        $this->price = $price;
        $this->discount = $discount;
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

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getBedSingle(): int
    {
        return $this->bedSingle;
    }

    public function setBedSingle(int $bedSingle): void
    {
        $this->bedSingle = $bedSingle;
    }

    public function getBedDouble(): int
    {
        return $this->bedDouble;
    }

    public function setBedDouble(int $bedDouble): void
    {
        $this->bedDouble = $bedDouble;
    }

    public function getCapacity(): int
    {
        return $this->capacity;
    }

    public function setCapacity(int $capacity): void
    {
        $this->capacity = $capacity;
    }

    public function isAvailable(): bool
    {
        return $this->available;
    }

    public function setAvailable(bool $available): void
    {
        $this->available = $available;
    }

    public function getType(): Type
    {
        return $this->type;
    }

    public function setType(Type $type): void
    {
        $this->type = $type;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function setPrice(float $price): void
    {
        $this->price = $price;
    }

    public function getDiscount(): float
    {
        return $this->discount;
    }

    public function setDiscount(float $discount): void
    {
        if ($discount < 0 || $discount > 100) {
            throw new InvalidArgumentException('Discount must be between 0 and 100.');
        }
        $this->discount = $discount;
    }

    public function getPriceAfterDiscount(): float
    {
        return $this->price - ($this->price * $this->discount / 100);
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
