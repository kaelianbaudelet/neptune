<?php
// src/Entity/BookingGuest.php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Booking;

class BookingGuest
{
    private Booking $booking;
    private string $name;
    private string $type;

    public function __construct(Booking $booking, string $name, string $type)
    {
        $this->booking = $booking;
        $this->name = $name;
        $this->type = $type;
    }

    public function getBooking(): Booking
    {
        return $this->booking;
    }

    public function setBooking(Booking $booking): void
    {
        $this->booking = $booking;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }
}
