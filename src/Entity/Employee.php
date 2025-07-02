<?php

declare(strict_types=1);

namespace App\Entity;

class Employee
{
    private ?string $id = null;
    private string $name;
    private string $email;
    private string $phone;
    private string $position;
    private float $salary;
    private \DateTime $dateOfBirth;
    private \DateTime $hiredAt;
    private \DateTime $createdAt;
    private \DateTime $updatedAt;

    public function __construct(
        ?string $id,
        string $name,
        string $email,
        string $phone,
        string $position,
        float $salary,
        \DateTime $dateOfBirth,
        \DateTime $hiredAt,
        \DateTime $createdAt,
        \DateTime $updatedAt
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->phone = $phone;
        $this->position = $position;
        $this->salary = $salary;
        $this->dateOfBirth = $dateOfBirth;
        $this->hiredAt = $hiredAt;
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
        $this->email = $email;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): void
    {
        $this->phone = $phone;
    }

    public function getPosition(): string
    {
        return $this->position;
    }

    public function setPosition(string $position): void
    {
        $this->position = $position;
    }

    public function getSalary(): float
    {
        return $this->salary;
    }

    public function setSalary(float $salary): void
    {
        $this->salary = $salary;
    }

    public function getDateOfBirth(): \DateTime
    {
        return $this->dateOfBirth;
    }

    public function setDateOfBirth(\DateTime $dateOfBirth): void
    {
        $this->dateOfBirth = $dateOfBirth;
    }

    public function getHiredAt(): \DateTime
    {
        return $this->hiredAt;
    }

    public function setHiredAt(\DateTime $hiredAt): void
    {
        $this->hiredAt = $hiredAt;
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
