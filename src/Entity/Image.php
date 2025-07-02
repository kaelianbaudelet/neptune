<?php

declare(strict_types=1);

namespace App\Entity;

class Image
{
    private ?string $id = null;
    private string $fileKey;
    private string $name;
    private string $extension;
    private ?string $description = null;
    private \DateTime $createdAt;
    private \DateTime $updatedAt;

    public function __construct(
        ?string $id,
        string $fileKey,
        string $name,
        string $extension,
        ?string $description,
        \DateTime $createdAt,
        \DateTime $updatedAt
    ) {
        $this->id = $id;
        $this->fileKey = $fileKey;
        $this->name = $name;
        $this->extension = $extension;
        $this->description = $description;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public function getId(): string
    {
        return $this->id;
    }

    function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getFileKey(): string
    {
        return $this->fileKey;
    }

    public function setFileKey(string $fileKey): void
    {
        $this->fileKey = $fileKey;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getExtension(): string
    {
        return $this->extension;
    }

    public function setExtension(string $extension): void
    {
        $this->extension = $extension;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
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
