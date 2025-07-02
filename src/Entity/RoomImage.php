<?php
// src/Entity/RoomImage.php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Room;
use App\Entity\Image;
use InvalidArgumentException;

class RoomImage
{
    private Room $room;
    private Image $image;

    public function __construct(Room $room, Image $image)
    {
        $this->room = $room;
        $this->image = $image;
    }

    public function getRoom(): Room
    {
        return $this->room;
    }

    public function getImage(): Image
    {
        return $this->image;
    }
}
