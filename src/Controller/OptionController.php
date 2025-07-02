<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\DependencyContainer;
use Twig\Environment;

use App\Entity\Option;

class OptionController
{
    private $twig;
    private $optionModel;
    private $imageModel;

    public function __construct(Environment $twig, DependencyContainer $dependencyContainer)
    {
        $this->twig = $twig;
        $this->optionModel = $dependencyContainer->get('OptionModel');
        $this->imageModel = $dependencyContainer->get('ImageModel');
    }

    public function options()
    {
        $options = $this->optionModel->getAllOptions();
        $images = $this->imageModel->getAllImages();

        echo $this->twig->render('optionController/options.html.twig', [
            'options' => $options,
            'images' => $images
        ]);
    }

    public function addOption()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = filter_var($_POST['name'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
            $description = filter_var($_POST['description'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
            $price = filter_var($_POST['price'] ?? 0, FILTER_VALIDATE_FLOAT);
            $tva = filter_var($_POST['tva'] ?? 20, FILTER_VALIDATE_FLOAT);
            $imageId = filter_var($_POST['image_id'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
            $perAdult = isset($_POST['per_adult']) ? (bool)$_POST['per_adult'] : false;
            $perChild = isset($_POST['per_child']) ? (bool)$_POST['per_child'] : false;
            $perNight = isset($_POST['per_night']) ? (bool)$_POST['per_night'] : false;
            $perQuantity = isset($_POST['per_quantity']) ? (bool)$_POST['per_quantity'] : false;
            $maxQuantity = filter_var($_POST['max_quantity'] ?? null, FILTER_VALIDATE_INT);
            $quantityIdentifier = filter_var($_POST['quantity_identifier'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
            $errors = [];

            if (empty($name)) {
                $errors['name'] = 'Le nom est requis.';
            }

            if (strlen($name) > 50) {
                $errors['name'] = 'Le nom ne doit pas dépasser 50 caractères.';
            }

            if (strlen($description) > 180) {
                $errors['description'] = 'La description ne doit pas dépasser 180 caractères.';
            }

            if ($price === false || $price <= 0) {
                $errors['price'] = 'Le prix doit être un nombre positif.';
            }

            if ($tva === false || $tva < 0) {
                $errors['tva'] = 'La TVA doit être un nombre positif ou zéro.';
            }

            if (!empty($errors)) {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'Veuillez corriger les erreurs dans le formulaire.',
                    'context' => 'modal',
                    'errors' => $errors
                ];
                header('Location: /dashboard/options?modal=create-option');
                exit;
            }

            $image = null;
            if (!empty($imageId)) {
                $image = $this->imageModel->getImageById($imageId);
            }

            $option = new Option(
                null,
                $name,
                $description,
                (float)$price,
                (float)$tva,
                $image,
                $perAdult,
                $perChild,
                $perNight,
                $perQuantity,
                $maxQuantity !== false ? $maxQuantity : null,
                $quantityIdentifier ?: null,
                new \DateTime(),
                new \DateTime()
            );

            try {
                $this->optionModel->createOption($option);
                $_SESSION['alert'] = [
                    'status' => 'success',
                    'message' => 'L\'option a été créée avec succès.',
                    'context' => 'global'
                ];
                header('Location: /dashboard/options');
                exit;
            } catch (\Exception $e) {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'Une erreur est survenue lors de la création de l\'option: ' . $e->getMessage(),
                    'context' => 'global'
                ];
                header('Location: /dashboard/options');
                exit;
            }
        }

        header('Location: /dashboard/options');
        exit;
    }

    public function editOption(int $optionId)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = filter_var($_POST['name'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
            $price = filter_var($_POST['price'] ?? 0, FILTER_VALIDATE_FLOAT);
            $tva = filter_var($_POST['tva'] ?? 0, FILTER_VALIDATE_FLOAT);
            $imageId = filter_var($_POST['image_id'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
            $perAdult = isset($_POST['per_adult']) ? (bool)$_POST['per_adult'] : false;
            $perChild = isset($_POST['per_child']) ? (bool)$_POST['per_child'] : false;
            $perNight = isset($_POST['per_night']) ? (bool)$_POST['per_night'] : false;
            $perQuantity = isset($_POST['per_quantity']) ? (bool)$_POST['per_quantity'] : false;
            $maxQuantity = filter_var($_POST['max_quantity'] ?? null, FILTER_VALIDATE_INT);
            $quantityIdentifier = filter_var($_POST['quantity_identifier'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);

            $errors = [];

            if (empty($name)) {
                $errors['name'] = 'Le nom est requis.';
            }

            if ($price === false || $price <= 0) {
                $errors['price'] = 'Le prix doit être un nombre positif.';
            }

            if ($tva === false || $tva < 0) {
                $errors['tva'] = 'La TVA doit être un nombre positif ou zéro.';
            }

            if (!empty($errors)) {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'Veuillez corriger les erreurs dans le formulaire.',
                    'context' => 'modal',
                    'errors' => $errors
                ];
                header('Location: /dashboard/options/edit/' . $optionId);
                exit;
            }

            $option = $this->optionModel->getOptionById($optionId);
            if (!$option) {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'L\'option n\'existe pas.',
                    'context' => 'global'
                ];
                header('Location: /dashboard/options');
                exit;
            }

            $image = null;
            if (!empty($imageId)) {
                $image = $this->imageModel->getImageById($imageId);
            }

            $option->setName($name);
            $option->setPrice((float)$price);
            $option->setTva((float)$tva);
            if ($image) {
                $option->setImage($image);
            }
            $option->setPerAdult($perAdult);
            $option->setPerChild($perChild);
            $option->setPerNight($perNight);
            $option->setPerQuantity($perQuantity);

            if (!$perQuantity) {
                $option->setMaxQuantity(null);
                $option->setQuantityIdentifier(null);
            } else {
                $option->setMaxQuantity($maxQuantity !== false ? $maxQuantity : null);
                $option->setQuantityIdentifier($quantityIdentifier ?: null);
            }

            $option->setUpdatedAt(new \DateTime());

            try {
                $this->optionModel->updateOption($option);
                $_SESSION['alert'] = [
                    'status' => 'success',
                    'message' => 'L\'option a été mise à jour avec succès.',
                    'context' => 'global'
                ];
                header('Location: /dashboard/options');
                exit;
            } catch (\Exception $e) {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'Une erreur est survenue lors de la mise à jour de l\'option: ' . $e->getMessage(),
                    'context' => 'global'
                ];
                header('Location: /dashboard/options');
                exit;
            }
        }

        $option = $this->optionModel->getOptionById($optionId);
        if (!$option) {
            $_SESSION['alert'] = [
                'status' => 'error',
                'message' => 'L\'option n\'existe pas.',
                'context' => 'global'
            ];
            header('Location: /dashboard/options');
            exit;
        }

        $images = $this->imageModel->getAllImages();

        echo $this->twig->render('optionController/editOption.html.twig', [
            'option' => $option,
            'images' => $images
        ]);
    }

    public function deleteOption(int $optionId)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $option = $this->optionModel->getOptionById($optionId);

            if (!$option) {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'L\'option n\'existe pas.',
                    'context' => 'global'
                ];
                header('Location: /dashboard/options');
                exit;
            }

            try {
                $this->optionModel->deleteOption($optionId);
                $_SESSION['alert'] = [
                    'status' => 'success',
                    'message' => 'L\'option a été supprimée avec succès.',
                    'context' => 'global'
                ];
            } catch (\Exception $e) {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'Une erreur est survenue lors de la suppression de l\'option: ' . $e->getMessage(),
                    'context' => 'global'
                ];
            }

            header('Location: /dashboard/options');
            exit;
        }

        header('Location: /dashboard/options');
        exit;
    }
}
