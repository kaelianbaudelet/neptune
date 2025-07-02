<?php

declare(strict_types=1);

namespace App\Entity;

class Equipment
{
    private ?int $id = null;
    private string $name;
    private string $icon;
    private \DateTime $createdAt;
    private \DateTime $updatedAt;

    public function __construct(
        ?int $id,
        string $name,
        string $icon,
        \DateTime $createdAt,
        \DateTime $updatedAt
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->icon = $icon;
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

    public function getIcon(): string
    {
        return $this->icon;
    }

    public function setIcon(string $icon): void
    {
        $this->icon = $icon;
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
