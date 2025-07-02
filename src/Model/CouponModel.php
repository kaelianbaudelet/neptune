<?php

declare(strict_types=1);

namespace App\Model;

use App\Entity\Coupon;
use App\Entity\User;
use PDO;

class CouponModel
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function createCoupon(Coupon $coupon): bool
    {
        $sql = "INSERT INTO Coupon (code, discount, used, max_use, user_id, expires_at, created_at, updated_at) VALUES
(:code, :discount, :used, :max_use, :user_id, :expires_at, :created_at, :updated_at)";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':code', $coupon->getCode());
        $stmt->bindValue(':discount', $coupon->getDiscount());
        $stmt->bindValue(':used', $coupon->getUsed());
        $stmt->bindValue(':max_use', $coupon->getMaxUse());
        $stmt->bindValue(':user_id', $coupon->getUser()?->getId());
        $stmt->bindValue(':expires_at', $coupon->getExpiresAt()?->format('Y-m-d H:i:s'));
        $stmt->bindValue(':created_at', $coupon->getCreatedAt()->format('Y-m-d H:i:s'));
        $stmt->bindValue(':updated_at', $coupon->getUpdatedAt()->format('Y-m-d H:i:s'));
        return $stmt->execute();
    }

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

    public function getCouponByCode(string $code): ?Coupon
    {
        $sql = "SELECT * FROM Coupon WHERE code = :code";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':code', $code);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }
        $user = null;
        if ($row['user_id'] !== null) {
            $user = $this->getUserById($row['user_id']);
        }
        return new Coupon(
            $row['id'],
            $row['code'],
            $row['discount'],
            $row['used'],
            $row['max_use'],
            $user,
            $row['expires_at'] ? new \DateTime($row['expires_at']) : null,
            new \DateTime($row['created_at']),
            new \DateTime($row['updated_at'])
        );
    }

    public function getCouponById(int $id): ?Coupon
    {
        $sql = "SELECT * FROM Coupon WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }
        $user = null;
        if ($row['user_id'] !== null) {
            $user = $this->getUserById($row['user_id']);
        }
        return new Coupon(
            $row['id'],
            $row['code'],
            $row['discount'],
            $row['used'],
            $row['max_use'],
            $user,
            $row['expires_at'] ? new \DateTime($row['expires_at']) : null,
            new \DateTime($row['created_at']),
            new \DateTime($row['updated_at'])
        );
    }

    public function updateCoupon(Coupon $coupon): bool
    {
        $sql = "UPDATE Coupon SET code = :code, discount = :discount, used = :used, max_use = :max_use, user_id = :user_id, expires_at = :expires_at, updated_at = :updated_at WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':code', $coupon->getCode());
        $stmt->bindValue(':discount', $coupon->getDiscount());
        $stmt->bindValue(':used', $coupon->getUsed());
        $stmt->bindValue(':max_use', $coupon->getMaxUse());
        $stmt->bindValue(':user_id', $coupon->getUser()?->getId());
        $stmt->bindValue(':expires_at', $coupon->getExpiresAt()?->format('Y-m-d H:i:s'));
        $stmt->bindValue(':updated_at', $coupon->getUpdatedAt()->format('Y-m-d H:i:s'));
        $stmt->bindValue(':id', $coupon->getId());
        return $stmt->execute();
    }

    public function getAllCoupons(): array
    {
        $sql = "SELECT * FROM Coupon";
        $stmt = $this->db->query($sql);
        $coupons = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $user = null;
            if ($row['user_id'] !== null) {
                $user = $this->getUserById($row['user_id']);
            }
            $coupons[] = new Coupon(
                $row['id'],
                $row['code'],
                $row['discount'],
                $row['used'],
                $row['max_use'],
                $user,
                $row['expires_at'] ? new \DateTime($row['expires_at']) : null,
                new \DateTime($row['created_at']),
                new \DateTime($row['updated_at'])
            );
        }
        return $coupons;
    }

    public function deleteCoupon(int $id): bool
    {
        $sql = "DELETE FROM Coupon WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id);
        return $stmt->execute();
    }
}
