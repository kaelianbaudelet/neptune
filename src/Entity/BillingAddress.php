<?php
// src/Entity/BillingAddress.php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\User;

class BillingAddress
{
    private ?string $id = null;
    private ?User $user = null;
    private string $name;
    private string $country;
    private string $address;
    private ?string $address2;
    private string $city;
    private ?string $state;
    private string $postalCode;
    private \DateTime $createdAt;

    public function __construct(
        ?string $id,
        ?User $user,
        string $name,
        string $country,
        string $address,
        ?string $address2,
        string $city,
        ?string $state,
        string $postalCode,
        \DateTime $createdAt
    ) {
        $this->id = $id;
        $this->user = $user;
        $this->name = $name;
        $this->country = $country;
        $this->address = $address;
        $this->address2 = $address2;
        $this->city = $city;
        $this->state = $state;
        $this->postalCode = $postalCode;
        $this->createdAt = $createdAt;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): void
    {
        $this->user = $user;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function setCountry(string $country): void
    {
        $this->country = $country;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function setAddress(string $address): void
    {
        $this->address = $address;
    }

    public function getAddress2(): ?string
    {
        return $this->address2;
    }

    public function setAddress2(?string $address2): void
    {
        $this->address2 = $address2;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function setCity(string $city): void
    {
        $this->city = $city;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(?string $state): void
    {
        $this->state = $state;
    }

    public function getPostalCode(): string
    {
        return $this->postalCode;
    }

    public function setPostalCode(string $postalCode): void
    {
        $this->postalCode = $postalCode;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }
}
