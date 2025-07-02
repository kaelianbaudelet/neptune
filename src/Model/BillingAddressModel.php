<?php
// src/Model/BillingAddressModel.php

declare(strict_types=1);

namespace App\Model;

use App\Entity\BillingAddress;
use App\Entity\User;
use PDO;

class BillingAddressModel
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Crée une nouvelle adresse de facturation et retourne son id.
     */
    public function createBillingAddress(BillingAddress $billingAddress): string
    {
        $sql = "INSERT INTO Billing_Address (user_id, name, country, address, address2, city, state, postal_code, created_at)
                VALUES (:user_id, :name, :country, :address, :address2, :city, :state, :postal_code, :created_at)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':user_id', $billingAddress->getUser()?->getId());
        $stmt->bindValue(':name', $billingAddress->getName());
        $stmt->bindValue(':country', $billingAddress->getCountry());
        $stmt->bindValue(':address', $billingAddress->getAddress());
        $stmt->bindValue(':address2', $billingAddress->getAddress2());
        $stmt->bindValue(':city', $billingAddress->getCity());
        $stmt->bindValue(':state', $billingAddress->getState());
        $stmt->bindValue(':postal_code', $billingAddress->getPostalCode());
        $stmt->bindValue(':created_at', $billingAddress->getCreatedAt()->format('Y-m-d H:i:s'));
        $stmt->execute();

        // Récupérer l'ID généré (UUID)
        $sql = "SELECT id FROM Billing_Address WHERE name = :name AND address = :address AND created_at = :created_at ORDER BY created_at DESC LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':name', $billingAddress->getName());
        $stmt->bindValue(':address', $billingAddress->getAddress());
        $stmt->bindValue(':created_at', $billingAddress->getCreatedAt()->format('Y-m-d H:i:s'));
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row['id'];
    }

    /**
     * Met à jour une adresse de facturation existante.
     */
    public function updateBillingAddress(BillingAddress $billingAddress): bool
    {
        $sql = "UPDATE Billing_Address SET
                    user_id = :user_id,
                    name = :name,
                    country = :country,
                    address = :address,
                    address2 = :address2,
                    city = :city,
                    state = :state,
                    postal_code = :postal_code
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $billingAddress->getId());
        $stmt->bindValue(':user_id', $billingAddress->getUser()?->getId());
        $stmt->bindValue(':name', $billingAddress->getName());
        $stmt->bindValue(':country', $billingAddress->getCountry());
        $stmt->bindValue(':address', $billingAddress->getAddress());
        $stmt->bindValue(':address2', $billingAddress->getAddress2());
        $stmt->bindValue(':city', $billingAddress->getCity());
        $stmt->bindValue(':state', $billingAddress->getState());
        $stmt->bindValue(':postal_code', $billingAddress->getPostalCode());
        return $stmt->execute();
    }

    /**
     * Supprime une adresse de facturation par son id.
     */
    public function deleteBillingAddress(string $id): bool
    {
        $sql = "DELETE FROM Billing_Address WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id);
        return $stmt->execute();
    }

    /**
     * Récupère toutes les adresses de facturation pour un utilisateur donné (par son id).
     * Retourne un tableau d'objets BillingAddress.
     */
    public function getAllBillingAddress(string $userId): array
    {
        $sql = "SELECT * FROM Billing_Address WHERE user_id = :user_id ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':user_id', $userId);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $user = $this->getUserById($userId);
        $addresses = [];
        foreach ($rows as $row) {
            $addresses[] = new BillingAddress(
                $row['id'],
                $user,
                $row['name'],
                $row['country'],
                $row['address'],
                $row['address2'],
                $row['city'],
                $row['state'],
                $row['postal_code'],
                new \DateTime($row['created_at'])
            );
        }
        return $addresses;
    }

    /**
     * Récupère une adresse de facturation par son id (si besoin).
     */
    public function getBillingAddressById(string $id): ?BillingAddress
    {
        $sql = "SELECT * FROM Billing_Address WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        $user = $row['user_id'] ? $this->getUserById($row['user_id']) : null;

        return new BillingAddress(
            $row['id'],
            $user,
            $row['name'],
            $row['country'],
            $row['address'],
            $row['address2'],
            $row['city'],
            $row['state'],
            $row['postal_code'],
            new \DateTime($row['created_at'])
        );
    }

    /**
     * Récupère l'utilisateur par son ID (copié de BookingModel)
     */
    private function getUserById(string $id): ?User
    {
        $sql = "SELECT * FROM User WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }
        return new User(
            $row['id'],
            $row['name'],
            $row['email'],
            $row['phone'],
            (bool)$row['commercial_email_consent'],
            $row['type'],
            $row['password'],
            (bool)$row['is_active'],
            $row['activation_token'],
            $row['activation_token_expires_at'] ? new \DateTime($row['activation_token_expires_at']) : null,
            $row['reset_token'],
            $row['reset_token_expires_at'] ? new \DateTime($row['reset_token_expires_at']) : null,
            $row['role'],
            new \DateTime($row['created_at']),
            new \DateTime($row['updated_at'])
        );
    }
}
