<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\DependencyContainer;
use Twig\Environment;

use App\Entity\Coupon;

class CouponController
{
    private $twig;
    private $couponModel;
    private $userModel;

    public function __construct(Environment $twig, DependencyContainer $dependencyContainer)
    {
        $this->twig = $twig;
        $this->couponModel = $dependencyContainer->get('CouponModel');
        $this->userModel = $dependencyContainer->get('UserModel');
    }

    public function coupons()
    {
        $coupons = $this->couponModel->getAllCoupons();
        $users = $this->userModel->getAllUsers();
        echo $this->twig->render('couponController/coupons.html.twig', [
            'coupons' => $coupons,
            'users' => $users
        ]);
    }

    public function addCoupon()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $code = filter_var($_POST['code'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
            $discount = filter_var($_POST['discount'] ?? 0, FILTER_VALIDATE_INT);
            $max_use = filter_var($_POST['max_use'] ?? 1, FILTER_VALIDATE_INT);
            $user_id = filter_var($_POST['user_id'] ?? null, FILTER_SANITIZE_SPECIAL_CHARS);
            $expires_at = filter_var($_POST['expires_at'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);


            $errors = [];

            if (empty($code)) {
                $errors['code'] = 'Le code est requis.';
            } elseif (strlen($code) > 50) {
                $errors['code'] = 'Le code ne doit pas dépasser 50 caractères.';
            }

            if ($discount <= 0 || $discount > 100) {
                $errors['discount'] = 'La réduction doit être entre 1 et 100.';
            }

            if ($max_use <= 0) {
                $errors['max_use'] = 'Le nombre d\'utilisation maximum doit être supérieur à 0.';
            }

            if ($user_id && !$this->userModel->getUserById($user_id)) {
                $errors['user_id'] = 'L\'utilisateur sélectionné n\'existe pas.';
            }

            if ($expires_at && !\DateTime::createFromFormat('Y-m-d', $expires_at)) {
                $errors['expires_at'] = 'La date d\'expiration est invalide.';
            }
            if ($expires_at && new \DateTime($expires_at) < new \DateTime()) {
                $errors['expires_at'] = 'La date d\'expiration doit être dans le futur.';
            }

            $existingCoupon = $this->couponModel->getCouponByCode($code);
            if ($existingCoupon && $existingCoupon->getId() !== null) {
                $errors['code'] = 'Le code de réduction existe déjà.';
            }

            if (!empty($errors)) {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'Veuillez corriger les erreurs dans le formulaire.',
                    'context' => 'modal',
                    'errors' => $errors
                ];
                header('Location: /dashboard/coupons?modal=create-coupon');
                exit;
            }

            $coupon = new Coupon(
                null,
                $code,
                $discount,
                0,
                $max_use,
                $this->userModel->getUserById($user_id) ?? null,
                $expires_at ? new \DateTime($expires_at) : null,
                new \DateTime(),
                new \DateTime()
            );

            $this->couponModel->createCoupon($coupon);

            $_SESSION['alert'] = [
                'status' => 'success',
                'message' => 'Le code de réduction a été créé avec succès.',
                'context' => 'global'
            ];
            header('Location: /dashboard/coupons');
            exit;
        }

        header('Location: /dashboard/coupons');
        exit;
    }

    public function deleteCoupon(int $coupon_id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $coupon = $this->couponModel->getCouponById((int) $coupon_id);

            if (!$coupon) {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'Le code de réduction n\'existe pas.',
                    'context' => 'global'
                ];
                header('Location: /dashboard/coupons');
                exit;
            }

            $this->couponModel->deleteCoupon((int) $coupon_id);
            $_SESSION['alert'] = [
                'status' => 'success',
                'message' => 'Le code de réduction a été supprimé avec succès.',
                'context' => 'global'
            ];
            header('Location: /dashboard/coupons');
            exit;
        }

        header('Location: /dashboard/coupons');
        exit;
    }

    public function editCoupon(int $coupon_id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $code = filter_var($_POST['code'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
            $discount = filter_var($_POST['discount'] ?? 0, FILTER_VALIDATE_INT);
            $max_use = filter_var($_POST['max_use'] ?? 1, FILTER_VALIDATE_INT);
            $user_id = filter_var($_POST['user_id'] ?? null, FILTER_SANITIZE_SPECIAL_CHARS);
            $expires_at = filter_var($_POST['expires_at'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);

            $errors = [];

            if (empty($code)) {
                $errors['code'] = 'Le code est requis.';
            } elseif (strlen($code) > 50) {
                $errors['code'] = 'Le code ne doit pas dépasser 50 caractères.';
            }

            if ($discount <= 0 || $discount > 100) {
                $errors['discount'] = 'La réduction doit être entre 1 et 100.';
            }

            if ($max_use <= 0) {
                $errors['max_use'] = 'Le nombre d\'utilisation maximum doit être supérieur à 0.';
            }

            if ($user_id && !$this->userModel->getUserById($user_id)) {
                $errors['user_id'] = 'L\'utilisateur sélectionné n\'existe pas.';
            }

            if ($expires_at && !\DateTime::createFromFormat('Y-m-d', $expires_at)) {
                $errors['expires_at'] = 'La date d\'expiration est invalide.';
            }
            if ($expires_at && new \DateTime($expires_at) < new \DateTime()) {
                $errors['expires_at'] = 'La date d\'expiration doit être dans le futur.';
            }

            $existingCoupon = $this->couponModel->getCouponByCode($code);
            if ($existingCoupon && $existingCoupon->getId() !== (int) $coupon_id) {
                $errors['code'] = 'Le code de réduction existe déjà.';
            }

            if (!empty($errors)) {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'Veuillez corriger les erreurs dans le formulaire.',
                    'context' => 'modal',
                    'errors' => $errors
                ];
                header('Location: /dashboard/coupons/edit/' . $coupon_id);
                exit;
            }

            $coupon = $this->couponModel->getCouponById((int) $coupon_id);
            if (!$coupon) {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'Le code de réduction n\'existe pas.',
                    'context' => 'global'
                ];
                header('Location: /dashboard/coupons');
                exit;
            }

            $user = $this->userModel->getUserById($user_id) ?? null;

            $coupon->setCode($code);
            $coupon->setDiscount($discount);
            $coupon->setMaxUse($max_use);
            $coupon->setUser($user);
            $coupon->setUpdatedAt(new \DateTime());

            $this->couponModel->updateCoupon($coupon);

            $_SESSION['alert'] = [
                'status' => 'success',
                'message' => 'Le code de réduction a été mis à jour avec succès.',
                'context' => 'global'
            ];
            header('Location: /dashboard/coupons');
            exit;
        }

        $coupon = $this->couponModel->getCouponById((int) $coupon_id);
        if (!$coupon) {
            $_SESSION['alert'] = [
                'status' => 'error',
                'message' => 'Le code de réduction n\'existe pas.',
                'context' => 'global'
            ];
            header('Location: /dashboard/coupons');
            exit;
        }

        $users = $this->userModel->getAllUsers();
        echo $this->twig->render('couponController/editCoupon.html.twig', [
            'coupon' => $coupon,
            'users' => $users
        ]);
    }
}
