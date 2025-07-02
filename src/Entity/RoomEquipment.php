<?php
// src/Entity/RoomEquipment.php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Room;
use App\Entity\Equipment;
use InvalidArgumentException;

class RoomEquipment
{
    private Room $room;
    private Equipment $equipment;

    public function __construct(Room $room, Equipment $equipment)
    {
        $this->room = $room;
        $this->equipment = $equipment;
    }

    public function getRoom(): Room
    {
        return $this->room;
    }

    public function getEquipment(): Equipment
    {
        return $this->equipment;
    }
}
