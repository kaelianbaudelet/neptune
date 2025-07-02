<?php

declare(strict_types=1);

namespace App\Model;

use App\Entity\Room;
use App\Entity\Type;
use App\Entity\Image;
use App\Entity\Equipment;
use PDO;

class RoomModel
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function createRoom(Room $room, array $images, array $equipments): bool
    {
        try {
            $this->db->beginTransaction();

            $sql = "INSERT INTO Room (name, description, bed_single, bed_double, capacity, available, type_id, price, discount, created_at, updated_at)
                    VALUES (:name, :description, :bed_single, :bed_double, :capacity, :available, :type_id, :price, :discount, :created_at, :updated_at)";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':name', $room->getName());
            $stmt->bindValue(':description', $room->getDescription());
            $stmt->bindValue(':bed_single', $room->getBedSingle());
            $stmt->bindValue(':bed_double', $room->getBedDouble());
            $stmt->bindValue(':capacity', $room->getCapacity());
            $stmt->bindValue(':available', (int) $room->isAvailable(), PDO::PARAM_INT);
            $type = $room->getType();
            if ($type === null) {
                throw new \Exception("Room type is not set.");
            }
            $stmt->bindValue(':type_id', $type->getId());
            $stmt->bindValue(':price', $room->getPrice());
            $stmt->bindValue(':discount', $room->getDiscount());
            $stmt->bindValue(':created_at', $room->getCreatedAt()->format('Y-m-d H:i:s'));
            $stmt->bindValue(':updated_at', $room->getUpdatedAt()->format('Y-m-d H:i:s'));
            $stmt->execute();

            $roomId = $this->db->lastInsertId();

            $sqlImage = "INSERT INTO Room_Image (room_id, image_id) VALUES (:room_id, :image_id)";
            $stmtImage = $this->db->prepare($sqlImage);

            foreach ($images as $image) {
                $stmtImage->bindValue(':room_id', $roomId);

                $stmtImage->bindValue(':image_id', $image->getId());
                $stmtImage->execute();
            }

            $sqlEquipment = "INSERT INTO Room_Equipment (room_id, equipment_id) VALUES (:room_id, :equipment_id)";
            $stmtEquipment = $this->db->prepare($sqlEquipment);

            foreach ($equipments as $equipment) {
                $stmtEquipment->bindValue(':room_id', $roomId);
                $stmtEquipment->bindValue(':equipment_id', $equipment->getId());
                $stmtEquipment->execute();
            }

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function getRoomById(int $id): ?Room
    {
        $sql = "SELECT * FROM Room WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        $type = $this->getTypeById($row['type_id']);
        if ($type === null) {
            throw new \Exception("Type not found for id " . $row['type_id']);
        }

        $room = new Room(
            $row['id'],
            $row['name'],
            $row['description'],
            $row['bed_single'],
            $row['bed_double'],
            $row['capacity'],
            (bool)$row['available'],
            $type,
            $row['price'],
            $row['discount'],
            new \DateTime($row['created_at']),
            new \DateTime($row['updated_at'])
        );

        $room->images = $this->getRoomImages($row['id']);
        $room->equipments = $this->getRoomEquipments($row['id']);

        return $room;
    }

    public function getRoomImages(int $roomId): array
    {
        $sql = "SELECT i.* FROM Image i
                JOIN Room_Image ri ON ri.image_id = i.id
                WHERE ri.room_id = :room_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':room_id', $roomId);
        $stmt->execute();

        $images = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $images[] = new Image(
                $row['id'],
                $row['file_key'],
                $row['name'],
                $row['extension'],
                $row['description'],
                new \DateTime($row['created_at']),
                new \DateTime($row['updated_at'])
            );
        }

        return $images;
    }

    public function getRoomEquipments(int $roomId): array
    {
        $sql = "SELECT e.* FROM Equipment e
                JOIN Room_Equipment re ON re.equipment_id = e.id
                WHERE re.room_id = :room_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':room_id', $roomId);
        $stmt->execute();

        $equipments = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $equipments[] = new Equipment(
                $row['id'],
                $row['name'],
                $row['icon'],
                new \DateTime($row['created_at']),
                new \DateTime($row['updated_at'])
            );
        }

        return $equipments;
    }

    public function getAllRooms(): array
    {
        $sql = "SELECT * FROM Room";
        $stmt = $this->db->query($sql);
        $rooms = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $type = $this->getTypeById($row['type_id']);
            $room = new Room(
                $row['id'],
                $row['name'],
                $row['description'],
                $row['bed_single'],
                $row['bed_double'],
                $row['capacity'],
                (bool)$row['available'],
                $type,
                $row['price'],
                $row['discount'],
                new \DateTime($row['created_at']),
                new \DateTime($row['updated_at'])
            );

            $room->images = $this->getRoomImages($row['id']);
            $room->equipments = $this->getRoomEquipments($row['id']);

            $rooms[] = $room;
        }

        return $rooms;
    }

    public function getAvailableRooms(\DateTime $arrivalDate, \DateTime $departureDate, int $numberOfGuests): array
    {
        $arrivalDateFormatted = $arrivalDate->format('Y-m-d');
        $departureDateFormatted = $departureDate->format('Y-m-d');

        $sql = "SELECT r.* FROM Room r
                WHERE r.capacity >= :numberOfGuests
                AND r.available = 1
                AND r.id NOT IN (
                    SELECT b.room_id FROM Booking b
                    WHERE (
                        (b.checkin <= :departureDate AND b.checkout >= :arrivalDate)
                    )
                )
                ORDER BY r.price ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':numberOfGuests', $numberOfGuests, PDO::PARAM_INT);
        $stmt->bindValue(':arrivalDate', $arrivalDateFormatted);
        $stmt->bindValue(':departureDate', $departureDateFormatted);
        $stmt->execute();

        $rooms = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $type = $this->getTypeById($row['type_id']);
            if ($type === null) {
                continue;
            }

            $room = new Room(
                $row['id'],
                $row['name'],
                $row['description'],
                $row['bed_single'],
                $row['bed_double'],
                $row['capacity'],
                (bool)$row['available'],
                $type,
                $row['price'],
                $row['discount'],
                new \DateTime($row['created_at']),
                new \DateTime($row['updated_at'])
            );

            $room->images = $this->getRoomImages($row['id']);
            $room->equipments = $this->getRoomEquipments($row['id']);

            $rooms[] = $room;
        }

        return $rooms;
    }

    public function isRoomAvailable(int $roomId, \DateTime $arrivalDate, \DateTime $departureDate): bool
    {
        $arrivalDateFormatted = $arrivalDate->format('Y-m-d');
        $departureDateFormatted = $departureDate->format('Y-m-d');

        $sql = "SELECT COUNT(*) FROM Booking b
                WHERE b.room_id = :room_id
                AND (
                    (b.checkin <= :departureDate AND b.checkout >= :arrivalDate)
                )";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':room_id', $roomId, PDO::PARAM_INT);
        $stmt->bindValue(':arrivalDate', $arrivalDateFormatted);
        $stmt->bindValue(':departureDate', $departureDateFormatted);
        $stmt->execute();

        return (int)$stmt->fetchColumn() === 0;
    }

    public function updateRoom(Room $room, array $images, array $equipments): bool
    {
        try {
            $this->db->beginTransaction();

            $sql = "UPDATE Room SET
                    name = :name,
                    description = :description,
                    bed_single = :bed_single,
                    bed_double = :bed_double,
                    capacity = :capacity,
                    available = :available,
                    type_id = :type_id,
                    price = :price,
                    discount = :discount,
                    updated_at = :updated_at
                    WHERE id = :id";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':name', $room->getName());
            $stmt->bindValue(':description', $room->getDescription());
            $stmt->bindValue(':bed_single', $room->getBedSingle());
            $stmt->bindValue(':bed_double', $room->getBedDouble());
            $stmt->bindValue(':capacity', $room->getCapacity());
            $stmt->bindValue(':available', $room->isAvailable(), PDO::PARAM_BOOL);
            $stmt->bindValue(':type_id', $room->getType()->getId());
            $stmt->bindValue(':price', $room->getPrice());
            $stmt->bindValue(':discount', $room->getDiscount());
            $stmt->bindValue(':updated_at', $room->getUpdatedAt()->format('Y-m-d H:i:s'));
            $stmt->bindValue(':id', $room->getId());
            $stmt->execute();

            $this->db->prepare("DELETE FROM Room_Image WHERE room_id = ?")->execute([$room->getId()]);
            $this->db->prepare("DELETE FROM Room_Equipment WHERE room_id = ?")->execute([$room->getId()]);

            $sqlImage = "INSERT INTO Room_Image (room_id, image_id) VALUES (:room_id, :image_id)";
            $stmtImage = $this->db->prepare($sqlImage);

            foreach ($images as $image) {
                $stmtImage->bindValue(':room_id', $room->getId());
                $stmtImage->bindValue(':image_id', $image->getId());
                $stmtImage->execute();
            }

            $sqlEquipment = "INSERT INTO Room_Equipment (room_id, equipment_id) VALUES (:room_id, :equipment_id)";
            $stmtEquipment = $this->db->prepare($sqlEquipment);

            foreach ($equipments as $equipment) {
                $stmtEquipment->bindValue(':room_id', $room->getId());
                $stmtEquipment->bindValue(':equipment_id', $equipment->getId());
                $stmtEquipment->execute();
            }

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function deleteRoom(int $id): bool
    {
        try {
            $this->db->beginTransaction();

            $this->db->prepare("DELETE FROM Room_Image WHERE room_id = ?")->execute([$id]);
            $this->db->prepare("DELETE FROM Room_Equipment WHERE room_id = ?")->execute([$id]);

            $sql = "DELETE FROM Room WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $id);
            $stmt->execute();

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    private function getTypeById(int $typeId): ?Type
    {
        $sql = "SELECT * FROM Type WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $typeId);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }
        return new Type($row['id'], $row['name']);
    }
}
