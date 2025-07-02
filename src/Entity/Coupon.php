<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\User;

use InvalidArgumentException;

class Coupon
{
    private ?int $id = null;
    private string $code;
    private int $discount;
    private int $used = 0;
    private int $maxUse = 1;
    private ?User $user = null;
    private ?\DateTime $expiresAt = null;
    private \DateTime $createdAt;
    private \DateTime $updatedAt;

    public function __construct(
        ?int $id,
        string $code,
        int $discount,
        ?int $used,
        int $maxUse,
        ?User $user,
        ?\DateTime $expiresAt,
        \DateTime $createdAt,
        \DateTime $updatedAt
    ) {
        $this->id = $id;
        $this->code = $code;
        $this->discount = $discount;
        $this->used = $used ?? 0;
        $this->maxUse = $maxUse;
        $this->user = $user;
        $this->expiresAt = $expiresAt;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): void
    {
        $this->code = $code;
    }

    public function getDiscount(): int
    {
        return $this->discount;
    }

    public function setDiscount(int $discount): void
    {
        $this->discount = $discount;
    }

    public function getUsed(): int
    {
        return $this->used;
    }

    public function setUsed(int $used): void
    {
        $this->used = $used;
    }

    public function getMaxUse(): int
    {
        return $this->maxUse;
    }

    public function setMaxUse(int $maxUse): void
    {
        $this->maxUse = $maxUse;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): void
    {
        $this->user = $user;
    }

    public function getExpiresAt(): ?\DateTime
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(?\DateTime $expiresAt): void
    {
        $this->expiresAt = $expiresAt;
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
