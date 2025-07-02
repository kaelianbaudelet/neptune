<?php
// src/Model/InvoiceModel.php

declare(strict_types=1);

namespace App\Model;

use App\Entity\Invoice;
use App\Entity\InvoiceOption;
use App\Entity\User;
use PDO;

class InvoiceModel
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function createInvoice(Invoice $invoice): string
    {
        try {
            $this->db->beginTransaction();

            $sql = "INSERT INTO Invoice (user_id, type, original_invoice_id, status, total_ttc, total_ht, total_tva, tva, coupon_code, coupon_discount, discount, checkin, checkout, adults, children, room_name, room_price, tourist_tax, billing_address, billing_address2, billing_city, billing_postal_code, billing_country, billing_phone, billing_email, billing_name, payment_id, payment_method, created_at)
                    VALUES (:user_id, :type, :original_invoice_id, :status, :total_ttc, :total_ht, :total_tva, :tva, :coupon_code, :coupon_discount, :discount, :checkin, :checkout, :adults, :children, :room_name, :room_price, :tourist_tax, :billing_address, :billing_address2, :billing_city, :billing_postal_code, :billing_country, :billing_phone, :billing_email, :billing_name, :payment_id, :payment_method, :created_at)";

            $stmt = $this->db->prepare($sql);

            $stmt->bindValue(':type', $invoice->getType());
            $stmt->bindValue(':original_invoice_id', $invoice->getOriginalInvoice()?->getId());
            $stmt->bindValue(':status', $invoice->getStatus());
            $stmt->bindValue(':user_id', $invoice->getUser()?->getId());
            $stmt->bindValue(':total_ttc', $invoice->getTotalTtc());
            $stmt->bindValue(':total_ht', $invoice->getTotalHt());
            $stmt->bindValue(':total_tva', $invoice->getTotalTva());
            $stmt->bindValue(':tva', $invoice->getTva());
            $stmt->bindValue(':coupon_code', $invoice->getCouponCode());
            $stmt->bindValue(':coupon_discount', $invoice->getCouponDiscount());
            $stmt->bindValue(':discount', $invoice->getDiscount());
            $stmt->bindValue(':checkin', $invoice->getCheckin()->format('Y-m-d'));
            $stmt->bindValue(':checkout', $invoice->getCheckout()->format('Y-m-d'));
            $stmt->bindValue(':adults', $invoice->getAdults());
            $stmt->bindValue(':children', $invoice->getChildren());
            $stmt->bindValue(':room_name', $invoice->getRoomName());
            $stmt->bindValue(':room_price', $invoice->getRoomPrice());
            $stmt->bindValue(':tourist_tax', $invoice->getTouristTax());
            $stmt->bindValue(':billing_address', $invoice->getBillingAddress());
            $stmt->bindValue(':billing_address2', $invoice->getBillingAddress2());
            $stmt->bindValue(':billing_city', $invoice->getBillingCity());
            $stmt->bindValue(':billing_postal_code', $invoice->getBillingPostalCode());
            $stmt->bindValue(':billing_country', $invoice->getBillingCountry());
            $stmt->bindValue(':billing_phone', $invoice->getBillingPhone());
            $stmt->bindValue(':billing_email', $invoice->getBillingEmail());
            $stmt->bindValue(':billing_name', $invoice->getBillingName());
            $stmt->bindValue(':payment_id', $invoice->getPaymentId());
            $stmt->bindValue(':payment_method', $invoice->getPaymentMethod());
            $stmt->bindValue(':created_at', $invoice->getCreatedAt()->format('Y-m-d H:i:s'));
            $stmt->execute();

            $sql = "SELECT id FROM Invoice WHERE created_at = :created_at ORDER BY created_at DESC LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':created_at', $invoice->getCreatedAt()->format('Y-m-d H:i:s'));
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $invoiceId = $result['id'];

            foreach ($invoice->getInvoiceOptions() as $option) {
                $sql = "INSERT INTO Invoice_Option (invoice_id, option_name, option_price, option_tva, per_adult, per_child, per_night, per_quantity, quantity_identifier, quantity)
                        VALUES (:invoice_id, :option_name, :option_price, :option_tva, :per_adult, :per_child, :per_night, :per_quantity, :quantity_identifier, :quantity)";
                $stmt = $this->db->prepare($sql);
                $stmt->bindValue(':invoice_id', $invoiceId);
                $stmt->bindValue(':option_name', $option->getOptionName());
                $stmt->bindValue(':option_price', $option->getOptionPrice());
                $stmt->bindValue(':option_tva', $option->getOptionTva());
                $stmt->bindValue(':per_adult', $option->isPerAdult(), PDO::PARAM_BOOL);
                $stmt->bindValue(':per_child', $option->isPerChild(), PDO::PARAM_BOOL);
                $stmt->bindValue(':per_night', $option->isPerNight(), PDO::PARAM_BOOL);
                $stmt->bindValue(':per_quantity', $option->isPerQuantity(), PDO::PARAM_BOOL);
                $stmt->bindValue(':quantity_identifier', $option->getQuantityIdentifier());
                $stmt->bindValue(':quantity', $option->getQuantity());
                $stmt->execute();
            }

            $this->db->commit();
            return $invoiceId;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function getInvoiceById(string $id): ?Invoice
    {
        $sql = "SELECT * FROM Invoice WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        $user = $row['user_id'] ? $this->getUserById($row['user_id']) : null;
        $originalInvoice = $row['original_invoice_id'] ? $this->getInvoiceById($row['original_invoice_id']) : null;


        $invoice = new Invoice(
            $row['id'],
            $row['type'],
            $originalInvoice,
            $row['status'],
            $user,
            $row['total_ttc'],
            $row['total_ht'],
            $row['total_tva'],
            $row['tva'],
            $row['coupon_code'],
            $row['coupon_discount'],
            (float)$row['discount'],
            new \DateTime($row['checkin']),
            new \DateTime($row['checkout']),
            $row['adults'],
            $row['children'],
            $row['room_name'],
            $row['room_price'],
            $row['tourist_tax'],
            $row['billing_address'],
            $row['billing_address2'],
            $row['billing_city'],
            $row['billing_postal_code'],
            $row['billing_country'],
            $row['billing_phone'],
            $row['billing_email'],
            $row['billing_name'],
            $row['payment_id'],
            $row['payment_method'],
            new \DateTime($row['created_at']),
        );

        $sql = "SELECT option_name, option_price, option_tva, per_adult, per_child, per_night, per_quantity, quantity_identifier, quantity FROM Invoice_Option WHERE invoice_id = :invoice_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':invoice_id', $id);
        $stmt->execute();
        $options = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($options as $optionRow) {
            $invoiceOption = new InvoiceOption(
                $optionRow['id'] ?? null,
                $invoice,
                $optionRow['option_name'],
                $optionRow['option_price'],
                $optionRow['option_tva'],
                (bool)$optionRow['per_adult'],
                (bool)$optionRow['per_child'],
                (bool)$optionRow['per_night'],
                (bool)$optionRow['per_quantity'],
                $optionRow['quantity_identifier'],
                $optionRow['quantity']
            );
            $invoice->addInvoiceOption($invoiceOption);
        }

        return $invoice;
    }

    public function getLastInvoice(): ?Invoice
    {
        $sql = "SELECT * FROM Invoice ORDER BY created_at DESC LIMIT 1";
        $stmt = $this->db->query($sql);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        $user = $row['user_id'] ? $this->getUserById($row['user_id']) : null;
        $originalInvoice = $row['original_invoice_id'] ? $this->getInvoiceById($row['original_invoice_id']) : null;
        $invoice = new Invoice(
            $row['id'],
            $row['type'],
            $originalInvoice,
            $row['status'],
            $user,
            $row['total_ttc'],
            $row['total_ht'],
            $row['total_tva'],
            $row['tva'],
            $row['coupon_code'],
            $row['coupon_discount'],
            $row['discount'],
            new \DateTime($row['checkin']),
            new \DateTime($row['checkout']),
            $row['adults'],
            $row['children'],
            $row['room_name'],
            $row['room_price'],
            $row['tourist_tax'],
            $row['billing_address'],
            $row['billing_address2'],
            $row['billing_city'],
            $row['billing_postal_code'],
            $row['billing_country'],
            $row['billing_phone'],
            $row['billing_email'],
            $row['billing_name'],
            $row['payment_id'],
            $row['payment_method'],
            new \DateTime($row['created_at']),
        );

        $sql = "SELECT option_name, option_price, option_tva, per_adult, per_child, per_night, per_quantity, quantity_identifier, quantity FROM Invoice_Option WHERE invoice_id = :invoice_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':invoice_id', $row['id']);
        $stmt->execute();
        $options = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($options as $optionRow) {
            $invoiceOption = new InvoiceOption(
                $optionRow['id'] ?? null,
                $invoice,
                $optionRow['option_name'],
                $optionRow['option_price'],
                $optionRow['option_tva'],
                (bool)$optionRow['per_adult'],
                (bool)$optionRow['per_child'],
                (bool)$optionRow['per_night'],
                (bool)$optionRow['per_quantity'],
                $optionRow['quantity_identifier'],
                $optionRow['quantity']
            );
            $invoice->addInvoiceOption($invoiceOption);
        }

        return $invoice;
    }

    public function getAllInvoices(): array
    {
        $sql = "SELECT * FROM Invoice";
        $invoiceStmt = $this->db->query($sql);
        $invoices = [];

        while ($row = $invoiceStmt->fetch(PDO::FETCH_ASSOC)) {

            $user = $row['user_id'] ? $this->getUserById($row['user_id']) : null;
            $originalInvoice = $row['original_invoice_id'] ? $this->getInvoiceById($row['original_invoice_id']) : null;

            $invoice = new Invoice(
                $row['id'],
                $row['type'],
                $originalInvoice,
                $row['status'],
                $user,
                $row['total_ttc'],
                $row['total_ht'],
                $row['total_tva'],
                $row['tva'],
                $row['coupon_code'],
                $row['coupon_discount'],
                $row['discount'],
                new \DateTime($row['checkin']),
                new \DateTime($row['checkout']),
                $row['adults'],
                $row['children'],
                $row['room_name'],
                $row['room_price'],
                $row['tourist_tax'],
                $row['billing_address'],
                $row['billing_address2'],
                $row['billing_city'],
                $row['billing_postal_code'],
                $row['billing_country'],
                $row['billing_phone'],
                $row['billing_email'],
                $row['billing_name'],
                $row['payment_id'],
                $row['payment_method'],
                new \DateTime($row['created_at'])
            );

            $optionsSql = "SELECT * FROM Invoice_Option WHERE invoice_id = :invoice_id";
            $optionsStmt = $this->db->prepare($optionsSql);
            $optionsStmt->bindValue(':invoice_id', $row['id']);
            $optionsStmt->execute();
            $options = $optionsStmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($options as $optionRow) {
                $invoiceOption = new InvoiceOption(
                    $optionRow['id'] ?? null,
                    $invoice,
                    $optionRow['option_name'],
                    $optionRow['option_price'],
                    $optionRow['option_tva'],
                    (bool)$optionRow['per_adult'],
                    (bool)$optionRow['per_child'],
                    (bool)$optionRow['per_night'],
                    (bool)$optionRow['per_quantity'],
                    $optionRow['quantity_identifier'],
                    $optionRow['quantity']
                );
                $invoice->addInvoiceOption($invoiceOption);
            }

            $invoices[] = $invoice;
        }

        return $invoices;
    }

    public function deleteInvoice(string $id): bool
    {
        try {
            $this->db->beginTransaction();

            $sql = "DELETE FROM Invoice_Option WHERE invoice_id = :invoice_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':invoice_id', $id);
            $stmt->execute();

            $sql = "DELETE FROM Invoice WHERE id = :id";
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
            $row['phone'],
            $row['commercial_email_consent'],
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
