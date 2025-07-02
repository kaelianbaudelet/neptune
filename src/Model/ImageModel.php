<?php

declare(strict_types=1);

namespace App\Model;

use App\Entity\Image;
use PDO;

class ImageModel
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function createImage(Image $image): bool
    {
        $sql = "INSERT INTO Image (file_key, name, extension, description, created_at, updated_at) VALUES
(:file_key, :name, :extension, :description, :created_at, :updated_at)";

        $stmt = $this->db->prepare($sql);

        $stmt->bindValue(':file_key', $image->getFileKey());
        $stmt->bindValue(':name', $image->getName());
        $stmt->bindValue(':extension', $image->getExtension());
        $stmt->bindValue(':description', $image->getDescription());
        $stmt->bindValue(':created_at', $image->getCreatedAt()->format('Y-m-d H:i:s'));
        $stmt->bindValue(':updated_at', $image->getUpdatedAt()->format('Y-m-d H:i:s'));
        return $stmt->execute();
    }

    public function getImageById(string $id): ?Image
    {
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

    public function updateImage(Image $image): bool
    {
        $sql = "UPDATE Image SET name = :name, description = :description, updated_at = :updated_at WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':name', $image->getName());
        $stmt->bindValue(':description', $image->getDescription());
        $stmt->bindValue(':updated_at', $image->getUpdatedAt()->format('Y-m-d H:i:s'));
        $stmt->bindValue(':id', $image->getId());
        return $stmt->execute();
    }

    public function getAllImages(): array
    {
        $sql = "SELECT * FROM Image";
        $stmt = $this->db->query($sql);
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

    public function deleteImage(string $id): bool
    {
        $sql = "DELETE FROM Image WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id);
        return $stmt->execute();
    }
}
