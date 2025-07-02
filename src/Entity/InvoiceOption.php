<?php

declare(strict_types=1);

namespace App\Entity;


class InvoiceOption
{
    private ?string $id = null;
    private Invoice $invoice;
    private string $optionName;
    private float $optionPrice;
    private float $optionTva;
    private bool $perAdult;
    private bool $perChild;
    private bool $perNight;
    private bool $perQuantity;
    private ?string $quantityIdentifier;
    private int $quantity;

    public function __construct(
        ?string $id,
        Invoice $invoice,
        string $optionName,
        float $optionPrice,
        float $optionTva,
        bool $perAdult,
        bool $perChild,
        bool $perNight,
        bool $perQuantity,
        ?string $quantityIdentifier,
        int $quantity
    ) {
        $this->id = $id;
        $this->invoice = $invoice;
        $this->optionName = $optionName;
        $this->optionPrice = $optionPrice;
        $this->optionTva = $optionTva;
        $this->perAdult = $perAdult;
        $this->perChild = $perChild;
        $this->perNight = $perNight;
        $this->perQuantity = $perQuantity;
        $this->quantityIdentifier = $quantityIdentifier;
        $this->quantity = $quantity;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getInvoice(): Invoice
    {
        return $this->invoice;
    }

    public function getOptionName(): string
    {
        return $this->optionName;
    }

    public function getOptionPrice(): float
    {
        return $this->optionPrice;
    }

    public function getOptionTva(): float
    {
        return $this->optionTva;
    }

    public function isPerAdult(): bool
    {
        return $this->perAdult;
    }

    public function isPerChild(): bool
    {
        return $this->perChild;
    }

    public function isPerNight(): bool
    {
        return $this->perNight;
    }

    public function isPerQuantity(): bool
    {
        return $this->perQuantity;
    }

    public function getQuantityIdentifier(): ?string
    {
        return $this->quantityIdentifier;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }
}
