<?php

declare(strict_types=1);

namespace App\Model;

use App\Entity\User;
use PDO;

class UserModel
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function createUser(User $user): bool
    {
        $sql = "INSERT INTO User (name, email, phone, commercial_email_consent, type, password, is_active, activation_token, activation_token_expires_at, role, created_at, updated_at) VALUES
(:name, :email, :phone, :commercial_email_consent, :type, :password, :is_active, :activation_token, :activation_token_expires_at, :role, :created_at, :updated_at)";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':name', $user->getName());
        $stmt->bindValue(':email', $user->getEmail());
        $stmt->bindValue(':phone', $user->getPhone());
        $stmt->bindValue(':password', $user->getPassword());
        $stmt->bindValue(':type', $user->getType());
        $stmt->bindValue(':is_active', $user->getIsActive(), PDO::PARAM_BOOL);
        $stmt->bindValue(':activation_token', $user->getActivationToken());
        $stmt->bindValue(':activation_token_expires_at', $user->getActivationTokenExpiresAt()?->format('Y-m-d H:i:s'));
        $stmt->bindValue(':role', $user->getRole());
        $stmt->bindValue(':created_at', $user->getCreatedAt()->format('Y-m-d H:i:s'));
        $stmt->bindValue(':updated_at', $user->getUpdatedAt()->format('Y-m-d H:i:s'));
        return $stmt->execute();
    }


    public function upsertUser(User $user): bool
    {
        $sql = "INSERT INTO User (name, email, phone, commercial_email_consent, type, password, is_active, activation_token, activation_token_expires_at, role, created_at, updated_at)
    VALUES (:name, :email, :phone, :commercial_email_consent, :type, :password, :is_active, :activation_token, :activation_token_expires_at, :role, :created_at, :updated_at)
    ON DUPLICATE KEY UPDATE
        name = VALUES(name),
        type = VALUES(type),
        password = VALUES(password),
        is_active = VALUES(is_active),
        activation_token = VALUES(activation_token),
        activation_token_expires_at = VALUES(activation_token_expires_at),
        role = VALUES(role),
        updated_at = VALUES(updated_at)";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':name', $user->getName());
        $stmt->bindValue(':email', $user->getEmail());
        $stmt->bindValue(':phone', $user->getPhone());
        $stmt->bindValue(':commercial_email_consent', $user->getCommercialEmailConsent(), PDO::PARAM_BOOL);
        $stmt->bindValue(':password', $user->getPassword());
        $stmt->bindValue(':type', $user->getType());
        $stmt->bindValue(':is_active', $user->getIsActive(), PDO::PARAM_BOOL);
        $stmt->bindValue(':activation_token', $user->getActivationToken());
        $stmt->bindValue(':activation_token_expires_at', $user->getActivationTokenExpiresAt()?->format('Y-m-d H:i:s'));
        $stmt->bindValue(':role', $user->getRole());
        $stmt->bindValue(':created_at', $user->getCreatedAt()->format('Y-m-d H:i:s'));
        $stmt->bindValue(':updated_at', $user->getUpdatedAt()->format('Y-m-d H:i:s'));

        return $stmt->execute();
    }


    public function getUserByEmail(string $email): ?User
    {
        $sql = "SELECT * FROM User WHERE email = :email";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':email', $email);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }
        return new User(
            $row['id'],
            $row['name'],
            $row['email'],
            $row['phone'] ?? null,
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

    public function getUserById(string $id): ?User
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
            $row['phone'] ?? null,
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

    public function updateUser(User $user): bool
    {
        $sql = "UPDATE User SET name = :name, email = :email, phone = :phone, commercial_email_consent = :commercial_email_consent, password = :password, is_active = :is_active, activation_token = :activation_token, activation_token_expires_at = :activation_token_expires_at, reset_token = :reset_token, reset_token_expires_at = :reset_token_expires_at, role = :role, updated_at = :updated_at WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':name', $user->getName());
        $stmt->bindValue(':email', $user->getEmail());
        $stmt->bindValue(':phone', $user->getPhone());
        $stmt->bindValue(':commercial_email_consent', $user->getCommercialEmailConsent(), PDO::PARAM_BOOL);
        $stmt->bindValue(':password', $user->getPassword());
        $stmt->bindValue(':is_active', $user->getIsActive(), PDO::PARAM_BOOL);
        $stmt->bindValue(':activation_token', $user->getActivationToken());
        $stmt->bindValue(':activation_token_expires_at', $user->getActivationTokenExpiresAt()?->format('Y-m-d H:i:s'));
        $stmt->bindValue(':reset_token', $user->getResetToken());
        $stmt->bindValue(':reset_token_expires_at', $user->getResetTokenExpiresAt()?->format('Y-m-d H:i:s'));
        $stmt->bindValue(':role', $user->getRole());
        $stmt->bindValue(':updated_at', $user->getUpdatedAt()->format('Y-m-d H:i:s'));
        $stmt->bindValue(':id', $user->getId());
        return $stmt->execute();
    }

    public function getAllUsers(): array
    {
        $sql = "SELECT * FROM User";
        $stmt = $this->db->query($sql);
        $users = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $users[] = new User(
                $row['id'],
                $row['name'],
                $row['email'],
                $row['phone'] ?? null,
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
        return $users;
    }

    public function deleteUser(string $id): bool
    {
        $sql = "DELETE FROM User WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id);
        return $stmt->execute();
    }
}
