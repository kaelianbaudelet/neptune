<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\DependencyContainer;
use Twig\Environment;

class InvoiceController
{
    private $twig;
    private $invoiceModel;
    private $userModel;
    private $optionModel;
    private $roomModel;
    private $couponModel;


    public function __construct(Environment $twig, DependencyContainer $dependencyContainer)
    {
        $this->twig = $twig;
        $this->invoiceModel = $dependencyContainer->get('InvoiceModel');
        $this->userModel = $dependencyContainer->get('UserModel');
        $this->optionModel = $dependencyContainer->get('OptionModel');
        $this->roomModel = $dependencyContainer->get('RoomModel');
        $this->couponModel = $dependencyContainer->get('CouponModel');
    }

    public function invoice(string $invoiceId)
    {
        $invoice = $this->invoiceModel->getInvoiceById($invoiceId);
        if (!$invoice) {
            $_SESSION['alert'] = [
                'status' => 'error',
                'message' => 'La facture n\'existe pas.',
                'context' => 'global'
            ];
            header('Location: /dashboard/invoices');
            exit;
        }
        echo $this->twig->render('invoiceController/invoice.html.twig', [
            'invoice' => $invoice,
        ]);
    }

    public function invoices()
    {
        $invoices = $this->invoiceModel->getAllInvoices();
        echo $this->twig->render('invoiceController/invoices.html.twig', [
            'invoices' => $invoices,
            'users' => $this->userModel->getAllUsers(),
            'options' => $this->optionModel->getAllOptions(),
            'rooms' => $this->roomModel->getAllRooms(),
            'coupons' => $this->couponModel->getAllCoupons(),
        ]);
    }

    public function addInvoice()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $type = filter_var($_POST['type'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
            $originalInvoiceId = filter_var($_POST['original_invoice_id'] ?? null, FILTER_SANITIZE_SPECIAL_CHARS);
            $status = filter_var($_POST['status'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
            $userId = filter_var($_POST['user_id'] ?? null, FILTER_SANITIZE_SPECIAL_CHARS);
            $totalTtc = filter_var($_POST['total_ttc'] ?? 0, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            $totalHt = filter_var($_POST['total_ht'] ?? 0, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            $totalTva = filter_var($_POST['total_tva'] ?? 0, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            $tva = filter_var($_POST['tva'] ?? 0, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            $couponCode = filter_var($_POST['coupon_code'] ?? null, FILTER_SANITIZE_SPECIAL_CHARS);
            $couponDiscount = filter_var($_POST['coupon_discount'] ?? 0, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            $discount = filter_var($_POST['discount'] ?? 0, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            $checkin = filter_var($_POST['checkin'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
            $checkout = filter_var($_POST['checkout'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
            $adults = filter_var($_POST['adults'] ?? 0, FILTER_SANITIZE_NUMBER_INT);
            $children = filter_var($_POST['children'] ?? 0, FILTER_SANITIZE_NUMBER_INT);
            $roomName = filter_var($_POST['room_name'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
            $roomPrice = filter_var($_POST['room_price'] ?? 0, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            $touristTax = filter_var($_POST['tourist_tax'] ?? 0, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            $billingAddress = filter_var($_POST['billing_address'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
            $billingAddress2 = filter_var($_POST['billing_address2'] ?? null, FILTER_SANITIZE_SPECIAL_CHARS);
            $billingCity = filter_var($_POST['billing_city'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
            $billingPostalCode = filter_var($_POST['billing_postal_code'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
            $billingCountry = filter_var($_POST['billing_country'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
            $billingPhone = filter_var($_POST['billing_phone'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
            $billingEmail = filter_var($_POST['billing_email'] ?? '', FILTER_SANITIZE_EMAIL);
            $billingName = filter_var($_POST['billing_name'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
            $paymentId = filter_var($_POST['payment_id'] ?? null, FILTER_SANITIZE_SPECIAL_CHARS);
            $paymentMethod = filter_var($_POST['payment_method'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
            $optionData = $_POST['options'] ?? [];

            $errors = [];

            if (empty($type)) {
                $errors['type'] = 'Le type de facture est requis.';
            }

            if (empty($status)) {
                $errors['status'] = 'Le statut est requis.';
            }

            if (empty($billingAddress)) {
                $errors['billing_address'] = 'L\'adresse de facturation est requise.';
            }

            if (empty($billingCity)) {
                $errors['billing_city'] = 'La ville de facturation est requise.';
            }

            if (empty($billingPostalCode)) {
                $errors['billing_postal_code'] = 'Le code postal de facturation est requis.';
            }

            if (empty($billingCountry)) {
                $errors['billing_country'] = 'Le pays de facturation est requis.';
            }

            if (empty($billingPhone)) {
                $errors['billing_phone'] = 'Le téléphone de facturation est requis.';
            }

            if (empty($billingEmail) || !filter_var($billingEmail, FILTER_VALIDATE_EMAIL)) {
                $errors['billing_email'] = 'Une adresse email valide est requise.';
            }

            if (empty($billingName)) {
                $errors['billing_name'] = 'Le nom de facturation est requis.';
            }

            if (!empty($errors)) {
                $_SESSION['form_errors'] = $errors;
                $_SESSION['form_data'] = $_POST;
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'Veuillez corriger les erreurs dans le formulaire.',
                    'context' => 'modal'
                ];
                header('Location: /dashboard/invoices/add');
                exit;
            }

            if (!in_array($paymentMethod, ['credit_card', 'paypal', 'stripe'])) {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'Le mode de paiement est invalide.',
                    'context' => 'modal'
                ];
                header('Location: /dashboard/invoices/add');
                exit;
            }

            $user = $userId ? $this->userModel->getUserById($userId) : null;
            $originalInvoice = $originalInvoiceId ? $this->invoiceModel->getInvoiceById($originalInvoiceId) : null;
            $invoice = new \App\Entity\Invoice(
                null,
                $type,
                $originalInvoice,
                $status,
                $user,
                (float)$totalTtc,
                (float)$totalHt,
                (float)$totalTva,
                (float)$tva,
                $couponCode,
                (float)$couponDiscount,
                (float)$discount,
                new \DateTime($checkin),
                new \DateTime($checkout),
                (int)$adults,
                (int)$children,
                $roomName,
                (float)$roomPrice,
                (float)$touristTax,
                $billingAddress,
                $billingAddress2,
                $billingCity,
                $billingPostalCode,
                $billingCountry,
                $billingPhone,
                $billingEmail,
                $billingName,
                $paymentId,
                $paymentMethod,
                new \DateTime()
            );

            foreach ($optionData as $option) {
                $optionName = filter_var($option['name'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
                $optionPrice = filter_var($option['price'] ?? 0, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                $optionTva = filter_var($option['tva'] ?? 0, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                $perAdult = isset($option['per_adult']) ? (bool)$option['per_adult'] : false;
                $perChild = isset($option['per_child']) ? (bool)$option['per_child'] : false;
                $perNight = isset($option['per_night']) ? (bool)$option['per_night'] : false;
                $perQuantity = isset($option['per_quantity']) ? (bool)$option['per_quantity'] : false;
                $quantityIdentifier = filter_var($option['quantity_identifier'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
                $quantity = filter_var($option['quantity'] ?? 1, FILTER_SANITIZE_NUMBER_INT);

                $invoiceOption = new \App\Entity\InvoiceOption(
                    null,
                    $invoice,
                    $optionName,
                    (float)$optionPrice,
                    (float)$optionTva,
                    $perAdult,
                    $perChild,
                    $perNight,
                    $perQuantity,
                    $quantityIdentifier,
                    (int)$quantity
                );

                $invoice->addInvoiceOption($invoiceOption);
            }

            try {
                $invoiceId = $this->invoiceModel->createInvoice($invoice);
                $_SESSION['alert'] = [
                    'status' => 'success',
                    'message' => 'La facture a été créée avec succès.',
                    'context' => 'modal'
                ];
                header('Location: /dashboard/invoices');
                exit;
            } catch (\Exception $e) {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'Une erreur est survenue lors de la création de la facture: ' . $e->getMessage(),
                    'context' => 'modal'
                ];
                header('Location: /dashboard/invoices');
                exit;
            }
        }

        $users = $this->userModel->getAllUsers();
        $options = $this->optionModel->getAllOptions();
        $rooms = $this->roomModel->getAllRooms();
        $coupons = $this->couponModel->getAllCoupons();

        echo $this->twig->render('invoiceController/addInvoice.html.twig', [
            'users' => $users,
            'options' => $options,
            'rooms' => $rooms,
            'coupons' => $coupons
        ]);
    }

    public function deleteInvoice($invoiceId)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $invoice = $this->invoiceModel->getInvoiceById($invoiceId);

            if (!$invoice) {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'La facture n\'existe pas.',
                    'context' => 'global'
                ];
                header('Location: /dashboard/invoices');
                exit;
            }

            $currentDate = new \DateTime();
            $invoiceDate = $invoice->getCreatedAt();
            $interval = $currentDate->diff($invoiceDate);
            $years = $interval->y;

            if ($years < 10) {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'Vous ne pouvez pas supprimer une facture de moins de 10 ans.',
                    'context' => 'global'
                ];
                header('Location: /dashboard/invoices');
                exit;
            }


            try {
                $this->invoiceModel->deleteInvoice($invoiceId);
                $_SESSION['alert'] = [
                    'status' => 'success',
                    'message' => 'La facture a été supprimée avec succès.',
                    'context' => 'modal'
                ];
            } catch (\Exception $e) {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'Une erreur est survenue lors de la suppression de la facture: ' . $e->getMessage(),
                    'context' => 'modal'
                ];
            }
        }

        header('Location: /dashboard/invoices');
        exit;
    }
}
