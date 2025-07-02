<?php
// src/Entity/Booking.php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Invoice;
use App\Entity\Room;
use App\Entity\User;

use InvalidArgumentException;

class Booking
{
    private ?string $id = null;
    private ?User $user = null;
    private Room $room;
    private ?Invoice $invoice;
    private \DateTime $checkin;
    private \DateTime $checkout;
    private int $adults;
    private int $children;
    private \DateTime $createdAt;
    private \DateTime $updatedAt;
    public array $options = [];
    public array $guests = [];

    public function __construct(
        ?string $id,
        ?User $user,
        Room $room,
        ?Invoice $invoice,
        \DateTime $checkin,
        \DateTime $checkout,
        int $adults,
        int $children,
        \DateTime $createdAt,
        \DateTime $updatedAt

    ) {
        $this->id = $id;
        $this->user = $user;
        $this->room = $room;
        $this->invoice = $invoice;
        $this->checkin = $checkin;
        $this->checkout = $checkout;
        $this->adults = $adults;
        $this->children = $children;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function getRoom(): Room
    {
        return $this->room;
    }

    public function setRoom(Room $room): void
    {
        $this->room = $room;
    }

    public function getInvoice(): ?Invoice
    {
        return $this->invoice;
    }

    public function setInvoice(?Invoice $invoice): void
    {
        $this->invoice = $invoice;
    }

    public function getCheckin(): \DateTime
    {
        return $this->checkin;
    }

    public function setCheckin(\DateTime $checkin): void
    {
        $this->checkin = $checkin;
    }

    public function getCheckout(): \DateTime
    {
        return $this->checkout;
    }

    public function setCheckout(\DateTime $checkout): void
    {
        $this->checkout = $checkout;
    }

    public function getAdults(): int
    {
        return $this->adults;
    }

    public function setAdults(int $adults): void
    {
        $this->adults = $adults;
    }

    public function getChildren(): int
    {
        return $this->children;
    }

    public function setChildren(int $children): void
    {
        $this->children = $children;
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

    public function addBookingOption(BookingOption $bookingOption): void
    {
        $this->options[] = $bookingOption;
    }

    public function getBookingOptions(): array
    {
        return $this->options;
    }

    public function addBookingGuest(BookingGuest $bookingGuest): void
    {
        $this->guests[] = $bookingGuest;
    }

    public function getBookingGuests(): array
    {
        return $this->guests;
    }
}
