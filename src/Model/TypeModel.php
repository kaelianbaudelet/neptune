<?php

declare(strict_types=1);

namespace App\Model;

use App\Entity\Type;
use PDO;

class TypeModel
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function createType(Type $type): bool
    {
        $sql = "INSERT INTO Type (id, name) VALUES (:id, :name)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $type->getId());
        $stmt->bindValue(':name', $type->getName());
        return $stmt->execute();
    }

    public function getTypeById(int $id): ?Type
    {
        $sql = "SELECT * FROM Type WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }
        return new Type(
            $row['id'],
            $row['name']
        );
    }

    public function updateType(Type $type): bool
    {
        $sql = "UPDATE Type SET name = :name WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':name', $type->getName());
        $stmt->bindValue(':id', $type->getId());
        return $stmt->execute();
    }

    public function getAllTypes(): array
    {
        $sql = "SELECT * FROM Type";
        $stmt = $this->db->query($sql);
        $types = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $types[] = new Type(
                $row['id'],
                $row['name']
            );
        }
        return $types;
    }

    public function deleteType(int $id): bool
    {
        $sql = "DELETE FROM Type WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id);
        return $stmt->execute();
    }
}
