<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\DependencyContainer;
use App\Entity\Booking;
use App\Entity\BookingOption;
use App\Entity\BookingGuest;
use Twig\Environment;

class BookingController
{
    private $twig;
    private $bookingModel;
    private $userModel;
    private $roomModel;
    private $optionModel;
    private $invoiceModel;
    private $couponModel;

    public function __construct(Environment $twig, DependencyContainer $dependencyContainer)
    {
        $this->twig = $twig;
        $this->bookingModel = $dependencyContainer->get('BookingModel');
        $this->userModel = $dependencyContainer->get('UserModel');
        $this->roomModel = $dependencyContainer->get('RoomModel');
        $this->optionModel = $dependencyContainer->get('OptionModel');
        $this->invoiceModel = $dependencyContainer->get('InvoiceModel');
        $this->couponModel = $dependencyContainer->get('CouponModel');
    }

    public function bookRoom()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            // Correction du warning : on vérifie l'existence de la clé avant d'y accéder
            $couponCode = isset($_POST['coupon_code']) ? $_POST['coupon_code'] : '';

            // Si le code promo n'est pas fourni, on affiche la page de succès (comportement d'origine)
            if (empty($couponCode)) {
                echo $this->twig->render('bookingController/bookSuccess.html.twig', []);
                exit;
            }

            $code = filter_var($couponCode, FILTER_SANITIZE_SPECIAL_CHARS);
            $coupon = $this->couponModel->getCouponByCode($code);

            if ($coupon && $coupon->getId() !== null) {
                // Vérification expiration
                $now = new \DateTime();
                $expiresAt = $coupon->getExpiresAt();
                if ($expiresAt !== null && $expiresAt < $now) {
                    $response = [
                        'status' => 'error',
                        'message' => 'Ce code de réduction est expiré.'
                    ];
                } else {
                    // Vérification utilisateur
                    $couponUser = $coupon->getUser();
                    $sessionUser = isset($_SESSION['user']) ? $_SESSION['user'] : null;
                    $userOk = false;
                    if ($couponUser === null) {
                        // Coupon utilisable par tout le monde
                        $userOk = true;
                    } else {
                        // Coupon réservé à un utilisateur
                        if ($sessionUser && isset($sessionUser['id']) && $couponUser->getId() == $sessionUser['id']) {
                            $userOk = true;
                        }
                    }

                    if ($userOk) {
                        $response = [
                            'status' => 'success',
                            'message' => 'Le code de réduction a été appliqué à votre réservation.',
                            'discount' => $coupon->getDiscount(),
                            'max_use' => $coupon->getMaxUse(),
                            'expires_at' => $expiresAt ? $expiresAt->format('Y-m-d') : null
                        ];
                    } else {
                        $response = [
                            'status' => 'error',
                            'message' => 'Ce code de réduction n\'est pas valable pour votre compte.'
                        ];
                    }
                }
            } else {
                $response = [
                    'status' => 'error',
                    'message' => 'Le code de réduction est invalide.'
                ];
            }

            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
        }

        $arrivalDate = filter_input(INPUT_GET, 'arrival_date', FILTER_SANITIZE_SPECIAL_CHARS) ?? '';
        $departureDate = filter_input(INPUT_GET, 'departure_date', FILTER_SANITIZE_SPECIAL_CHARS) ?? '';
        $numberOfGuests = filter_input(INPUT_GET, 'number_of_guests', FILTER_SANITIZE_NUMBER_INT) ?? '';
        $roomId = filter_input(INPUT_GET, 'room_id', FILTER_SANITIZE_NUMBER_INT) ?? '';

        $room = $this->roomModel->getRoomById((int)$roomId);
        if (!$room) {
            $_SESSION['alert'] = [
                'status' => 'error',
                'message' => 'La chambre n\'existe pas.',
                'context' => 'global'
            ];
            header('Location: /search');
            exit;
        }

        if (empty($arrivalDate) || empty($departureDate) || empty($numberOfGuests) || empty($roomId)) {
            $_SESSION['alert'] = [
                'status' => 'error',
                'message' => 'Des informations nécessaires sont manquantes pour effectuer la réservation.',
                'context' => 'global'
            ];
            header('Location: /search');
            exit;
        }

        // Sécurité pour empêcher de manipuler le nombre d'invités
        $capacity = $room->getCapacity();
        if ($capacity < $numberOfGuests) {
            $_SESSION['alert'] = [
                'status' => 'error',
                'message' => 'Le nombre d\'invités dépasse la capacité de la chambre.',
                'context' => 'global'
            ];
            header('Location: /search');
            exit;
        }

        $arrivalDateTime = \DateTime::createFromFormat('Y-m-d', $arrivalDate);
        $departureDateTime = \DateTime::createFromFormat('Y-m-d', $departureDate);
        if (!$arrivalDateTime || !$departureDateTime || $arrivalDateTime >= $departureDateTime) {
            $_SESSION['alert'] = [
                'status' => 'error',
                'message' => 'Les dates d\'arrivée et de départ sont invalides.',
                'context' => 'global'
            ];
            header('Location: /search');
            exit;
        }

        if (!$room->isAvailable()) {
            $_SESSION['alert'] = [
                'status' => 'error',
                'message' => 'La chambre n\'est pas disponible',
                'context' => 'global'
            ];
            header('Location: /search');
            exit;
        }

        if (!$this->roomModel->isRoomAvailable((int)$roomId, $arrivalDateTime, $departureDateTime)) {
            $_SESSION['alert'] = [
                'status' => 'error',
                'message' => 'La chambre n\'est pas disponible pour les dates sélectionnées.',
                'context' => 'global'
            ];
            header('Location: /search');
            exit;
        }

        $nights = $arrivalDateTime->diff($departureDateTime)->days;
        if ($nights <= 0) {
            $_SESSION['alert'] = [
                'status' => 'error',
                'message' => 'La durée du séjour doit être d\'au moins une nuit.',
                'context' => 'global'
            ];
            header('Location: /search');
            exit;
        }

        echo $this->twig->render('bookingController/bookingStepper.html.twig', [
            'arrival_date' => $arrivalDate,
            'departure_date' => $departureDate,
            'number_of_guests' => $numberOfGuests,
            'room' => $this->roomModel->getRoomById((int)$roomId),
            'options' => $this->optionModel->getAllOptions(),
            'nights' => $nights,
        ]);
    }

    public function bookings()
    {
        $bookings = $this->bookingModel->getAllBookings();
        echo $this->twig->render('bookingController/bookings.html.twig', [
            'bookings' => $bookings,
            'users' => $this->userModel->getAllUsers(),
            'options' => $this->optionModel->getAllOptions(),
            'rooms' => $this->roomModel->getAllRooms(),
        ]);
    }

    public function addBooking()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Collecte et verification des données du formulaire
            $userId = filter_var($_POST['user_id'] ?? null, FILTER_SANITIZE_SPECIAL_CHARS);
            $roomId = filter_var($_POST['room_id'] ?? null, FILTER_SANITIZE_NUMBER_INT);
            $invoiceId = filter_var($_POST['invoice_id'] ?? null, FILTER_SANITIZE_SPECIAL_CHARS);
            $checkin = filter_var($_POST['checkin'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
            $checkout = filter_var($_POST['checkout'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
            $optionData = $_POST['options'] ?? [];
            $guestData = $_POST['guests'] ?? [];

            $adults = 0;
            $children = 0;
            foreach ($guestData as $guest) {
                $type = filter_var($guest['type'] ?? 'adult', FILTER_SANITIZE_SPECIAL_CHARS);
                if ($type === 'adult') {
                    $adults++;
                } elseif ($type === 'child') {
                    $children++;
                }
            }

            // Validation des données
            $errors = [];

            if (empty($roomId)) {
                $errors['room_id'] = 'La chambre est requise.';
            }

            if (empty($checkin)) {
                $errors['checkin'] = 'La date d\'arrivée est requise.';
            }

            if (empty($checkout)) {
                $errors['checkout'] = 'La date de départ est requise.';
            }

            // Si des erreurs de validation existent, rediriger vers la page de réservation avec un message d'erreur
            if (!empty($errors)) {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'Erreur lors de la création de la réservation.',
                    'context' => 'global'
                ];
                $_SESSION['form_errors'] = $errors;
                $_SESSION['form_data'] = $_POST;
                header('Location: /dashboard/bookings');
                exit;
            }

            // On récupère l'utilisateur si userId est fourni
            $user = $userId ? $this->userModel->getUserById($userId) : null;

            // On récupère la chambre
            $room = $this->roomModel->getRoomById((int)$roomId);
            if (!$room) {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'La chambre n\'existe pas.',
                    'context' => 'global'
                ];
                header('Location: /dashboard/bookings');
                exit;
            }

            // On récupère la facture si invoiceId est fourni
            $invoice = $invoiceId ? $this->invoiceModel->getInvoiceById($invoiceId) : null;

            // On crée la réservation
            $booking = new Booking(
                null,
                $user,
                $room,
                $invoice,
                new \DateTime($checkin),
                new \DateTime($checkout),
                $adults,
                $children,
                new \DateTime(),
                new \DateTime()
            );

            // Ajout des options de réservation
            foreach ($optionData as $option) {
                $optionId = filter_var($option['id'] ?? 0, FILTER_SANITIZE_NUMBER_INT);
                $quantity = filter_var($option['quantity'] ?? 1, FILTER_SANITIZE_NUMBER_INT);

                $optionEntity = $this->optionModel->getOptionById((int)$optionId);
                if ($optionEntity) {
                    $booking->addBookingOption(new BookingOption($booking, $optionEntity, (int)$quantity));
                }
            }

            // Ajout des invités
            foreach ($guestData as $guest) {
                $name = filter_var($guest['name'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
                $type = filter_var($guest['type'] ?? 'adult', FILTER_SANITIZE_SPECIAL_CHARS);

                if (!empty($name)) {
                    // Generate a unique ID for each guest to avoid primary key collisions
                    $uniqueId = uniqid('guest_', true);
                    $booking->addBookingGuest(new BookingGuest($booking, $name, $type, $uniqueId));
                }
            }

            // Sauvegarde de la réservation dans la base de données
            try {
                $bookingId = $this->bookingModel->createBooking($booking);
                $_SESSION['alert'] = [
                    'status' => 'success',
                    'message' => 'Réservation créée avec succès.',
                    'context' => 'global'
                ];
                header('Location: /dashboard/bookings');
                exit;
            } catch (\Exception $e) {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'Erreur lors de la création de la réservation: ' . $e->getMessage(),
                    'context' => 'global'
                ];
                header('Location: /dashboard/bookings');
                exit;
            }
        }

        header('Location: /dashboard/bookings');
        exit;
    }

    public function editBooking(string $bookingId)
    {
        $booking = $this->bookingModel->getBookingById($bookingId);
        if (!$booking) {
            $_SESSION['alert'] = [
                'status' => 'error',
                'message' => 'La réservation n\'existe pas.',
                'context' => 'global'
            ];
            header('Location: /dashboard/bookings');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Collecte et verification des données du formulaire
            $userId = filter_var($_POST['user_id'] ?? null, FILTER_SANITIZE_SPECIAL_CHARS);
            $roomId = filter_var($_POST['room_id'] ?? null, FILTER_SANITIZE_NUMBER_INT);
            $invoiceId = filter_var($_POST['invoice_id'] ?? null, FILTER_SANITIZE_SPECIAL_CHARS);
            $checkin = filter_var($_POST['checkin'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
            $checkout = filter_var($_POST['checkout'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
            $optionData = $_POST['options'] ?? [];
            $guestData = $_POST['guests'] ?? [];

            $adults = 0;
            $children = 0;
            foreach ($guestData as $guest) {
                $type = filter_var($guest['type'] ?? 'adult', FILTER_SANITIZE_SPECIAL_CHARS);
                if ($type === 'adult') {
                    $adults++;
                } elseif ($type === 'child') {
                    $children++;
                }
            }

            // Validation des données
            $errors = [];

            if (empty($roomId)) {
                $errors['room_id'] = 'La chambre est requise.';
            }

            if (empty($checkin)) {
                $errors['checkin'] = 'La date d\'arrivée est requise.';
            }

            if (empty($checkout)) {
                $errors['checkout'] = 'La date de départ est requise.';
            }

            // Si des erreurs de validation existent, rediriger vers la page de réservation avec un message d'erreur
            if (!empty($errors)) {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'Erreur lors de la modification de la réservation.',
                    'context' => 'global'
                ];
                $_SESSION['form_errors'] = $errors;
                $_SESSION['form_data'] = $_POST;
                header('Location: /dashboard/bookings/edit/' . $bookingId);
                exit;
            }

            // On récupère l'utilisateur si userId est fourni
            $user = $userId ? $this->userModel->getUserById($userId) : null;

            // On récupère la chambre
            $room = $this->roomModel->getRoomById((int)$roomId);
            if (!$room) {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'La chambre n\'existe pas.',
                    'context' => 'global'
                ];
                header('Location: /dashboard/bookings/edit/' . $bookingId);
                exit;
            }

            // On récupère la facture si invoiceId est fourni
            $invoice = $invoiceId ? $this->invoiceModel->getInvoiceById($invoiceId) : null;

            // On met à jour la réservation
            $booking->setUser($user);
            $booking->setRoom($room);
            $booking->setInvoice($invoice);
            $booking->setCheckin(new \DateTime($checkin));
            $booking->setCheckout(new \DateTime($checkout));
            $booking->setAdults($adults);
            $booking->setChildren($children);
            $booking->setUpdatedAt(new \DateTime());

            $booking->options = [];
            foreach ($optionData as $option) {
                $optionId = filter_var($option['id'] ?? 0, FILTER_SANITIZE_NUMBER_INT);
                $quantity = filter_var($option['quantity'] ?? 1, FILTER_SANITIZE_NUMBER_INT);

                $optionEntity = $this->optionModel->getOptionById((int)$optionId);
                if ($optionEntity) {
                    $booking->addBookingOption(new BookingOption($booking, $optionEntity, (int)$quantity));
                }
            }

            // Ajout des invités
            $booking->guests = [];
            foreach ($guestData as $guest) {
                $name = filter_var($guest['name'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
                $type = filter_var($guest['type'] ?? 'adult', FILTER_SANITIZE_SPECIAL_CHARS);

                if (!empty($name)) {
                    $booking->addBookingGuest(new BookingGuest($booking, $name, $type));
                }
            }

            // Sauvegarde de la réservation dans la base de données
            try {
                $this->bookingModel->updateBooking($booking);
                $_SESSION['alert'] = [
                    'status' => 'success',
                    'message' => 'Réservation modifiée avec succès.',
                    'context' => 'global'
                ];
                header('Location: /dashboard/booking/' . $bookingId);
                exit;
            } catch (\Exception $e) {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'Erreur lors de la modification de la réservation: ' . $e->getMessage(),
                    'context' => 'global'
                ];
                header('Location: /dashboard/bookings/edit/' . $bookingId);
                exit;
            }
        }

        // Affichage du formulaire d'édition de réservation
        $users = $this->userModel->getAllUsers();
        $options = $this->optionModel->getAllOptions();
        $rooms = $this->roomModel->getAllRooms();

        echo $this->twig->render('bookingController/editBooking.html.twig', [
            'booking' => $booking,
            'users' => $users,
            'options' => $options,
            'rooms' => $rooms
        ]);
    }

    public function deleteBooking(string $bookingId)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $booking = $this->bookingModel->getBookingById($bookingId);

            if (!$booking) {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'La réservation n\'existe pas.',
                    'context' => 'global'
                ];
                header('Location: /dashboard/bookings');
                exit;
            }

            try {
                $this->bookingModel->deleteBooking($bookingId);
                $_SESSION['alert'] = [
                    'status' => 'success',
                    'message' => 'Réservation supprimée avec succès.',
                    'context' => 'global'
                ];
            } catch (\Exception $e) {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'Erreur lors de la suppression de la réservation: ' . $e->getMessage(),
                    'context' => 'global'
                ];
            }
        }

        header('Location: /dashboard/bookings');
        exit;
    }
}
