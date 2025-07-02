<?php
// src/Entity/BookingOption.php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Booking;
use App\Entity\Option;

class BookingOption
{
    private Booking $booking;
    private Option $option;
    private int $quantity;

    public function __construct(Booking $booking, Option $option, int $quantity = 1)
    {
        $this->booking = $booking;
        $this->option = $option;
        $this->quantity = $quantity;
    }

    public function getBooking(): Booking
    {
        return $this->booking;
    }

    public function setBooking(Booking $booking): void
    {
        $this->booking = $booking;
    }

    public function getOption(): Option
    {
        return $this->option;
    }

    public function setOption(Option $option): void
    {
        $this->option = $option;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): void
    {
        $this->quantity = $quantity;
    }
}
