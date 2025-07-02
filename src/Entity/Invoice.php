<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\User;
use App\Entity\InvoiceOption;

class Invoice
{
    private ?string $id = null;
    private string $type;
    private ?Invoice $originalInvoice = null;
    private string $status;
    private ?User $user = null;
    private float $totalTtc;
    private float $totalHt;
    private float $totalTva;
    private float $tva;
    private ?string $couponCode;
    private float $couponDiscount;
    private float $discount;
    private \DateTime $checkin;
    private \DateTime $checkout;
    private int $adults;
    private int $children;
    private string $roomName;
    private float $roomPrice;
    private float $touristTax;
    private string $billingAddress;
    private ?string $billingAddress2;
    private string $billingCity;
    private string $billingPostalCode;
    private string $billingCountry;
    private string $billingPhone;
    private string $billingEmail;
    private string $billingName;
    private ?string $paymentId;
    private string $paymentMethod;
    private \DateTime $createdAt;
    public array $options = [];

    public function __construct(
        ?string $id,
        string $type,
        ?Invoice $originalInvoice,
        string $status,
        ?User $user,
        float $totalTtc,
        float $totalHt,
        float $totalTva,
        float $tva,
        ?string $couponCode,
        float $couponDiscount,
        float $discount,
        \DateTime $checkin,
        \DateTime $checkout,
        int $adults,
        int $children,
        string $roomName,
        float $roomPrice,
        float $touristTax,
        string $billingAddress,
        ?string $billingAddress2,
        string $billingCity,
        string $billingPostalCode,
        string $billingCountry,
        string $billingPhone,
        string $billingEmail,
        string $billingName,
        ?string $paymentId,
        string $paymentMethod,
        \DateTime $createdAt,
    ) {
        $this->id = $id;
        $this->type = $type;
        $this->originalInvoice = $originalInvoice;
        $this->status = $status;
        $this->user = $user;
        $this->totalTtc = $totalTtc;
        $this->totalHt = $totalHt;
        $this->totalTva = $totalTva;
        $this->tva = $tva;
        $this->couponCode = $couponCode;
        $this->couponDiscount = $couponDiscount;
        $this->discount = $discount;
        $this->checkin = $checkin;
        $this->checkout = $checkout;
        $this->adults = $adults;
        $this->children = $children;
        $this->roomName = $roomName;
        $this->roomPrice = $roomPrice;
        $this->touristTax = $touristTax;
        $this->billingAddress = $billingAddress;
        $this->billingAddress2 = $billingAddress2;
        $this->billingCity = $billingCity;
        $this->billingPostalCode = $billingPostalCode;
        $this->billingCountry = $billingCountry;
        $this->billingPhone = $billingPhone;
        $this->billingEmail = $billingEmail;
        $this->billingName = $billingName;
        $this->paymentId = $paymentId;
        $this->paymentMethod = $paymentMethod;
        $this->createdAt = $createdAt;
    }

    public function getTotalTva(): float
    {
        return $this->totalTva;
    }

    public function getTva(): float
    {
        return $this->tva;
    }

    public function getCouponCode(): ?string
    {
        return $this->couponCode;
    }

    public function getCouponDiscount(): float
    {
        return $this->couponDiscount;
    }

    public function getDiscount(): float
    {
        return $this->discount;
    }

    public function getCheckin(): \DateTime
    {
        return $this->checkin;
    }

    public function getCheckout(): \DateTime
    {
        return $this->checkout;
    }

    public function getAdults(): int
    {
        return $this->adults;
    }

    public function getChildren(): int
    {
        return $this->children;
    }

    public function getRoomName(): string
    {
        return $this->roomName;
    }

    public function getRoomPrice(): float
    {
        return $this->roomPrice;
    }

    public function getTouristTax(): float
    {
        return $this->touristTax;
    }

    public function getPaymentId(): ?string
    {
        return $this->paymentId;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function getTotalTtc(): float
    {
        return $this->totalTtc;
    }

    public function getTotalHt(): float
    {
        return $this->totalHt;
    }

    public function getBillingAddress(): string
    {
        return $this->billingAddress;
    }

    public function getBillingAddress2(): ?string
    {
        return $this->billingAddress2;
    }

    public function getBillingCity(): string
    {
        return $this->billingCity;
    }

    public function getBillingPostalCode(): string
    {
        return $this->billingPostalCode;
    }

    public function getBillingCountry(): string
    {
        return $this->billingCountry;
    }

    public function getBillingPhone(): string
    {
        return $this->billingPhone;
    }

    public function getBillingEmail(): string
    {
        return $this->billingEmail;
    }

    public function getBillingName(): string
    {
        return $this->billingName;
    }

    public function getPaymentMethod(): string
    {
        return $this->paymentMethod;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function addInvoiceOption(InvoiceOption $option): void
    {
        $this->options[] = $option;
    }

    public function getInvoiceOptions(): array
    {
        return $this->options;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getOriginalInvoice(): ?Invoice
    {
        return $this->originalInvoice;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void // pour update le status de la facture
    {
        $this->status = $status;
    }
}
