<?php

declare(strict_types=1);

namespace App\Entity;

use InvalidArgumentException;

class User
{
    private ?string $id = null;
    private string $name;
    private string $email;
    private ?string $phone;
    private bool $commercialEmailConsent;
    private string $type;
    private string $password;
    private bool $isActive = false;
    private ?string $activationToken = null;
    private ?\DateTime $activationTokenExpiresAt = null;
    private ?string $resetToken = null;
    private ?\DateTime $resetTokenExpiresAt = null;
    private string $role;
    private \DateTime $createdAt;
    private \DateTime $updatedAt;

    public function __construct(
        ?string $id,
        string $name,
        string $email,
        ?string $phone,
        bool $commercialEmailConsent,
        string $type,
        string $password,
        bool $isActive,
        ?string $activationToken,
        ?\DateTime $activationTokenExpiresAt,
        ?string $resetToken,
        ?\DateTime $resetTokenExpiresAt,
        string $role,
        \DateTime $createdAt,
        \DateTime $updatedAt
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->phone = $phone;
        $this->commercialEmailConsent = $commercialEmailConsent;
        $this->type = $type;
        $this->password = $password;
        $this->isActive = $isActive;
        $this->activationToken = $activationToken;
        $this->activationTokenExpiresAt = $activationTokenExpiresAt;
        $this->resetToken = $resetToken;
        $this->resetTokenExpiresAt = $resetTokenExpiresAt;
        $this->role = $role;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public function getId(): ?string
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

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Email invalide.");
        }
        $this->email = $email;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): void
    {
        if ($phone !== null && !preg_match('/^\+?[0-9\s]+$/', $phone)) {
            throw new InvalidArgumentException("Numéro de téléphone invalide.");
        }
        $this->phone = $phone;
    }

    public function getCommercialEmailConsent(): bool
    {
        return $this->commercialEmailConsent;
    }

    public function setCommercialEmailConsent(bool $commercialEmailConsent): void
    {
        $this->commercialEmailConsent = $commercialEmailConsent;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = password_hash($password, PASSWORD_DEFAULT);
    }

    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->password);
    }

    public function getIsActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): void
    {
        $this->isActive = $isActive;
    }

    public function getActivationToken(): ?string
    {
        return $this->activationToken;
    }

    public function setActivationToken(?string $activationToken): void
    {
        $this->activationToken = $activationToken;
    }

    public function getActivationTokenExpiresAt(): ?\DateTime
    {
        return $this->activationTokenExpiresAt;
    }

    public function setResetToken(?string $resetToken): void
    {
        $this->resetToken = $resetToken;
    }

    public function getResetToken(): ?string
    {
        return $this->resetToken;
    }

    public function setResetTokenExpiresAt(?\DateTime $resetTokenExpiresAt): void
    {
        $this->resetTokenExpiresAt = $resetTokenExpiresAt;
    }

    public function getResetTokenExpiresAt(): ?\DateTime
    {
        return $this->resetTokenExpiresAt;
    }

    public function setActivationTokenExpiresAt(?\DateTime $activationTokenExpiresAt): void
    {
        $this->activationTokenExpiresAt = $activationTokenExpiresAt;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function setRole(string $role): void
    {
        $this->role = $role;
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
