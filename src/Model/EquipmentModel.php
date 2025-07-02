<?php

declare(strict_types=1);

namespace App\Model;

use App\Entity\Equipment;
use PDO;

class EquipmentModel
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function createEquipment(Equipment $equipment): bool
    {
        $sql = "INSERT INTO Equipment (id, name, icon, created_at, updated_at) VALUES (:id, :name, :icon, :created_at, :updated_at)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $equipment->getId());
        $stmt->bindValue(':name', $equipment->getName());
        $stmt->bindValue(':icon', $equipment->getIcon());
        $stmt->bindValue(':created_at', $equipment->getCreatedAt()->format('Y-m-d H:i:s'));
        $stmt->bindValue(':updated_at', $equipment->getUpdatedAt()->format('Y-m-d H:i:s'));
        return $stmt->execute();
    }

    public function getEquipmentById(int $id): ?Equipment
    {
        $sql = "SELECT * FROM Equipment WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }
        return new Equipment(
            $row['id'],
            $row['name'],
            $row['icon'],
            new \DateTime($row['created_at']),
            new \DateTime($row['updated_at'])
        );
    }

    public function updateEquipment(Equipment $equipment): bool
    {
        $sql = "UPDATE Equipment SET name = :name, icon = :icon, updated_at = :updated_at WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':name', $equipment->getName());
        $stmt->bindValue(':icon', $equipment->getIcon());
        $stmt->bindValue(':updated_at', $equipment->getUpdatedAt()->format('Y-m-d H:i:s'));
        $stmt->bindValue(':id', $equipment->getId());
        return $stmt->execute();
    }

    public function getAllEquipments(): array
    {
        $sql = "SELECT * FROM Equipment";
        $stmt = $this->db->query($sql);
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

    public function deleteEquipment(int $id): bool
    {
        $sql = "DELETE FROM Equipment WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id);
        return $stmt->execute();
    }
}
