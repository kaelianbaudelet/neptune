<?php

declare(strict_types=1);

namespace App\Model;

use App\Entity\Option;
use App\Entity\Image;
use PDO;

class OptionModel
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function createOption(Option $option): bool
    {
        try {
            $this->db->beginTransaction();

            $sql = "INSERT INTO Option (name, description, price, tva, image_id, per_adult, per_child, per_night, per_quantity, max_quantity, quantity_identifier, created_at, updated_at)
                    VALUES (:name, :description, :price, :tva, :image_id, :per_adult, :per_child, :per_night, :per_quantity, :max_quantity, :quantity_identifier, :created_at, :updated_at)";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':name', $option->getName());
            $stmt->bindValue(':description', $option->getDescription());
            $stmt->bindValue(':price', $option->getPrice());
            $stmt->bindValue(':tva', $option->getTva());
            $stmt->bindValue(':image_id', $option->getImage() ? $option->getImage()->getId() : null);
            $stmt->bindValue(':per_adult', $option->isPerAdult(), PDO::PARAM_BOOL);
            $stmt->bindValue(':per_child', $option->isPerChild(), PDO::PARAM_BOOL);
            $stmt->bindValue(':per_night', $option->isPerNight(), PDO::PARAM_BOOL);
            $stmt->bindValue(':per_quantity', $option->isPerQuantity(), PDO::PARAM_BOOL);
            $stmt->bindValue(':max_quantity', $option->getMaxQuantity());
            $stmt->bindValue(':quantity_identifier', $option->getQuantityIdentifier());
            $stmt->bindValue(':created_at', $option->getCreatedAt()->format('Y-m-d H:i:s'));
            $stmt->bindValue(':updated_at', $option->getUpdatedAt()->format('Y-m-d H:i:s'));
            $stmt->execute();

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function getOptionById(int $id): ?Option
    {
        $sql = "SELECT * FROM Option WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        $image = $this->getImageById($row['image_id']);

        return new Option(
            $row['id'],
            $row['name'],
            $row['description'],
            $row['price'],
            $row['tva'],
            $image,
            (bool)$row['per_adult'],
            (bool)$row['per_child'],
            (bool)$row['per_night'],
            (bool)$row['per_quantity'],
            $row['max_quantity'],
            $row['quantity_identifier'],
            new \DateTime($row['created_at']),
            new \DateTime($row['updated_at'])
        );
    }

    public function getAllOptions(): array
    {
        $sql = "SELECT * FROM Option";
        $stmt = $this->db->query($sql);
        $options = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $image = $this->getImageById($row['image_id']);
            $options[] = new Option(
                $row['id'],
                $row['name'],
                $row['description'],
                $row['price'],
                $row['tva'],
                $image,
                (bool)$row['per_adult'],
                (bool)$row['per_child'],
                (bool)$row['per_night'],
                (bool)$row['per_quantity'],
                $row['max_quantity'],
                $row['quantity_identifier'],
                new \DateTime($row['created_at']),
                new \DateTime($row['updated_at'])
            );
        }

        return $options;
    }

    public function updateOption(Option $option): bool
    {
        try {
            $this->db->beginTransaction();

            $sql = "UPDATE Option SET
                name = :name,
                description = :description,
                price = :price,
                tva = :tva,
                image_id = :image_id,
                per_adult = :per_adult,
                per_child = :per_child,
                per_night = :per_night,
                per_quantity = :per_quantity,
                max_quantity = :max_quantity,
                quantity_identifier = :quantity_identifier,
                updated_at = :updated_at
                WHERE id = :id";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':name', $option->getName());
            $stmt->bindValue(':description', $option->getDescription());
            $stmt->bindValue(':price', $option->getPrice());
            $stmt->bindValue(':tva', $option->getTva());
            $stmt->bindValue(':image_id', $option->getImage() ? $option->getImage()->getId() : null);
            $stmt->bindValue(':per_adult', $option->isPerAdult(), PDO::PARAM_BOOL);
            $stmt->bindValue(':per_child', $option->isPerChild(), PDO::PARAM_BOOL);
            $stmt->bindValue(':per_night', $option->isPerNight(), PDO::PARAM_BOOL);
            $stmt->bindValue(':per_quantity', $option->isPerQuantity(), PDO::PARAM_BOOL);
            $stmt->bindValue(':max_quantity', $option->getMaxQuantity());
            $stmt->bindValue(':quantity_identifier', $option->getQuantityIdentifier());
            $stmt->bindValue(':updated_at', $option->getUpdatedAt()->format('Y-m-d H:i:s'));
            $stmt->bindValue(':id', $option->getId());
            $stmt->execute();

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function deleteOption(int $id): bool
    {
        try {
            $this->db->beginTransaction();

            $sql = "DELETE FROM Option WHERE id = :id";
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

    private function getImageById(?string $id): ?Image
    {
        if ($id === null) {
            return null;
        }

        $sql = "SELECT * FROM Image WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return new Image(
            $row['id'],
            $row['file_key'],
            $row['name'],
            $row['extension'],
            $row['description'],
            new \DateTime($row['created_at']),
            new \DateTime($row['updated_at'])
        );
    }
}
