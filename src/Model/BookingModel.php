<?php
// src/Model/BookingModel.php

declare(strict_types=1);

namespace App\Model;

use App\Entity\Booking;
use App\Entity\BookingOption;
use App\Entity\BookingGuest;
use App\Entity\InvoiceOption;
use App\Entity\Room;
use App\Entity\User;
use App\Entity\Invoice;
use App\Entity\Option;
use App\Entity\Image;
use App\Entity\Type;
use PDO;

class BookingModel
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function createBooking(Booking $booking): string
    {
        try {
            $this->db->beginTransaction();

            $sql = "INSERT INTO Booking (user_id, room_id, invoice_id, checkin, checkout, adults, children, created_at, updated_at)
                    VALUES (:user_id, :room_id, :invoice_id, :checkin, :checkout, :adults, :children, :created_at, :updated_at)";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':user_id', $booking->getUser()?->getId());
            $stmt->bindValue(':room_id', $booking->getRoom()->getId());
            $stmt->bindValue(':invoice_id', $booking->getInvoice() ? $booking->getInvoice()->getId() : null);
            $stmt->bindValue(':checkin', $booking->getCheckin()->format('Y-m-d'));
            $stmt->bindValue(':checkout', $booking->getCheckout()->format('Y-m-d'));
            $stmt->bindValue(':adults', $booking->getAdults());
            $stmt->bindValue(':children', $booking->getChildren());
            $stmt->bindValue(':created_at', $booking->getCreatedAt()->format('Y-m-d H:i:s'));
            $stmt->bindValue(':updated_at', $booking->getUpdatedAt()->format('Y-m-d H:i:s'));
            $stmt->execute();

            $sql = "SELECT id FROM Booking WHERE created_at = :created_at ORDER BY created_at DESC LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':created_at', $booking->getCreatedAt()->format('Y-m-d H:i:s'));
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $bookingId = $result['id'];

            foreach ($booking->getBookingOptions() as $option) {
                $sql = "INSERT INTO Booking_Option (booking_id, option_id, quantity)
                        VALUES (:booking_id, :option_id, :quantity)";
                $stmt = $this->db->prepare($sql);
                $stmt->bindValue(':booking_id', $bookingId);
                $stmt->bindValue(':option_id', $option->getOption()->getId());
                $stmt->bindValue(':quantity', $option->getQuantity());
                $stmt->execute();
            }

            foreach ($booking->getBookingGuests() as $guest) {
                $sql = "INSERT INTO Booking_Guest (booking_id, name, type)
                        VALUES (:booking_id, :name, :type)";
                $stmt = $this->db->prepare($sql);
                $stmt->bindValue(':booking_id', $bookingId);
                $stmt->bindValue(':name', $guest->getName());
                $stmt->bindValue(':type', $guest->getType());
                $stmt->execute();
            }

            $this->db->commit();
            return $bookingId;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function updateBooking(Booking $booking): bool
    {
        try {
            $this->db->beginTransaction();

            $sql = "UPDATE Booking SET user_id = :user_id, room_id = :room_id, invoice_id = :invoice_id, checkin = :checkin, checkout = :checkout, adults = :adults, children = :children, updated_at = :updated_at
                    WHERE id = :id";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $booking->getId());
            $stmt->bindValue(':user_id', $booking->getUser()?->getId());
            $stmt->bindValue(':room_id', $booking->getRoom()->getId());
            $stmt->bindValue(':invoice_id', $booking->getInvoice() ? $booking->getInvoice()->getId() : null);
            $stmt->bindValue(':checkin', $booking->getCheckin()->format('Y-m-d'));
            $stmt->bindValue(':checkout', $booking->getCheckout()->format('Y-m-d'));
            $stmt->bindValue(':adults', $booking->getAdults());
            $stmt->bindValue(':children', $booking->getChildren());
            $stmt->bindValue(':updated_at', $booking->getUpdatedAt()->format('Y-m-d H:i:s'));
            $stmt->execute();

            $sql = "DELETE FROM Booking_Option WHERE booking_id = :booking_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':booking_id', $booking->getId());
            $stmt->execute();

            foreach ($booking->getBookingOptions() as $option) {
                $sql = "INSERT INTO Booking_Option (booking_id, option_id, quantity)
                        VALUES (:booking_id, :option_id, :quantity)";
                $stmt = $this->db->prepare($sql);
                $stmt->bindValue(':booking_id', $booking->getId());
                $stmt->bindValue(':option_id', $option->getOption()->getId());
                $stmt->bindValue(':quantity', $option->getQuantity());
                $stmt->execute();
            }

            $sql = "DELETE FROM Booking_Guest WHERE booking_id = :booking_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':booking_id', $booking->getId());
            $stmt->execute();

            foreach ($booking->getBookingGuests() as $guest) {
                $sql = "INSERT INTO Booking_Guest (booking_id, name, type)
                        VALUES (:booking_id, :name, :type)";
                $stmt = $this->db->prepare($sql);
                $stmt->bindValue(':booking_id', $booking->getId());
                $stmt->bindValue(':name', $guest->getName());
                $stmt->bindValue(':type', $guest->getType());
                $stmt->execute();
            }

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function getBookingById(string $id): ?Booking
    {
        $sql = "SELECT * FROM Booking WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        $user = null;
        if ($row['user_id']) {
            $user = $this->getUserById($row['user_id']);
        }
        $room = $this->getRoomById($row['room_id']);
        $invoice = $row['invoice_id'] ? $this->getInvoiceById($row['invoice_id']) : null;

        if ($room === null) {
            throw new \Exception("Room not found for booking id " . $row['id']);
        }

        $booking = new Booking(
            $row['id'],
            $user,
            $room,
            $invoice,
            new \DateTime($row['checkin']),
            new \DateTime($row['checkout']),
            $row['adults'],
            $row['children'],
            new \DateTime($row['created_at']),
            new \DateTime($row['updated_at'])
        );

        $sql = "SELECT * FROM Booking_Option WHERE booking_id = :booking_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':booking_id', $id);
        $stmt->execute();
        $options = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($options as $optionRow) {
            $option = $this->getOptionById($optionRow['option_id']);
            $bookingOption = new BookingOption(
                $booking,
                $option,
                $optionRow['quantity']
            );
            $booking->addBookingOption($bookingOption);
        }

        $guestsSql = "SELECT * FROM Booking_Guest WHERE booking_id = :booking_id";
        $guestsStmt = $this->db->prepare($guestsSql);
        $guestsStmt->bindValue(':booking_id', $id);
        $guestsStmt->execute();
        $guests = $guestsStmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($guests as $guestRow) {
            $bookingGuest = new BookingGuest(
                $booking,
                $guestRow['name'],
                $guestRow['type']
            );
            $booking->addBookingGuest($bookingGuest);
        }

        return $booking;
    }

    public function getAllBookings(): array
    {
        $sql = "SELECT * FROM Booking";
        $bookingStmt = $this->db->query($sql);
        $bookings = [];

        while ($row = $bookingStmt->fetch(PDO::FETCH_ASSOC)) {
            $user = null;
            if ($row['user_id']) {
                $user = $this->getUserById($row['user_id']);
            }
            $room = $this->getRoomById($row['room_id']);
            $invoice = $row['invoice_id'] ? $this->getInvoiceById($row['invoice_id']) : null;

            if ($room === null) {
                throw new \Exception("Room not found for booking id " . $row['id']);
            }

            $booking = new Booking(
                $row['id'],
                $user,
                $room,
                $invoice,
                new \DateTime($row['checkin']),
                new \DateTime($row['checkout']),
                $row['adults'],
                $row['children'],
                new \DateTime($row['created_at']),
                new \DateTime($row['updated_at'])
            );

            $optionsSql = "SELECT * FROM Booking_Option WHERE booking_id = :booking_id";
            $optionsStmt = $this->db->prepare($optionsSql);
            $optionsStmt->bindValue(':booking_id', $row['id']);
            $optionsStmt->execute();
            $options = $optionsStmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($options as $optionRow) {
                $option = $this->getOptionById($optionRow['option_id']);
                $bookingOption = new BookingOption(
                    $booking,
                    $option,
                    $optionRow['quantity']
                );
                $booking->addBookingOption($bookingOption);
            }

            $guestsSql = "SELECT * FROM Booking_Guest WHERE booking_id = :booking_id";
            $guestsStmt = $this->db->prepare($guestsSql);
            $guestsStmt->bindValue(':booking_id', $row['id']);
            $guestsStmt->execute();
            $guests = $guestsStmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($guests as $guestRow) {
                $bookingGuest = new BookingGuest(
                    $booking,
                    $guestRow['name'],
                    $guestRow['type']
                );
                $booking->addBookingGuest($bookingGuest);
            }

            $bookings[] = $booking;
        }

        return $bookings;
    }

    public function deleteBooking(string $id): bool
    {
        try {
            $this->db->beginTransaction();

            $sql = "DELETE FROM Booking_Guest WHERE booking_id = :booking_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':booking_id', $id);
            $stmt->execute();

            $sql = "DELETE FROM Booking_Option WHERE booking_id = :booking_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':booking_id', $id);
            $stmt->execute();

            $sql = "DELETE FROM Booking WHERE id = :id";
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

    private function getRoomById(int $id): ?Room
    {
        $sql = "SELECT * FROM Room WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }
        return new Room(
            $row['id'],
            $row['name'],
            $row['description'],
            $row['bed_single'],
            $row['bed_double'],
            $row['capacity'],
            (bool)$row['available'],
            $this->getTypeById($row['type_id']),
            $row['price'],
            $row['discount'],
            new \DateTime($row['created_at']),
            new \DateTime($row['updated_at'])
        );
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
            $row['description'],
            $row['name'],
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

    private function getTypeById(int $id): ?Type
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
            $row['name'],
        );
    }
}
