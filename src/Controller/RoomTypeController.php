<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\DependencyContainer;
use Twig\Environment;

use App\Entity\Type;

class RoomTypeController
{
    private $twig;
    private $typeModel;

    public function __construct(Environment $twig, DependencyContainer $dependencyContainer)
    {
        $this->twig = $twig;
        $this->typeModel = $dependencyContainer->get('TypeModel');
    }

    public function roomTypes()
    {
        $roomTypes = $this->typeModel->getAllTypes();
        echo $this->twig->render('roomTypeController/roomtypes.html.twig', [
            'roomTypes' => $roomTypes
        ]);
    }

    public function addRoomType()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = filter_var($_POST['name'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);

            $errors = [];

            if (empty($name)) {
                $errors['name'] = 'Le nom est requis.';
            } elseif (strlen($name) > 50) {
                $errors['name'] = 'Le nom ne doit pas dépasser 50 caractères.';
            }

            if (!empty($errors)) {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'Veuillez corriger les erreurs dans le formulaire.',
                    'context' => 'modal',
                    'errors' => $errors
                ];
                header('Location: /dashboard/roomtypes?modal=create-roomtype');
                exit;
            }

            $type = new Type(
                null,
                $name
            );

            $this->typeModel->createType($type);

            $_SESSION['alert'] = [
                'status' => 'success',
                'message' => 'Le type de chambre a été créé avec succès.',
                'context' => 'global'
            ];
            header('Location: /dashboard/roomtypes');
            exit;
        }

        header('Location: /dashboard/roomtypes');
        exit;
    }

    // deleteRoomType

    public function deleteRoomType($roomTypeId)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $roomType = $this->typeModel->getTypeById((int) $roomTypeId);
            if (!$roomType) {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'Le type de chambre n\'existe pas.',
                    'context' => 'global'
                ];
                header('Location: /dashboard/roomtypes');
                exit;
            }

            $this->typeModel->deleteType((int) $roomTypeId);
            $_SESSION['alert'] = [
                'status' => 'success',
                'message' => 'Le type de chambre a été supprimé avec succès.',
                'context' => 'global'
            ];
            header('Location: /dashboard/roomtypes');
            exit;
        }

        header('Location: /dashboard/roomtypes');
        exit;
    }

    public function editRoomType($roomTypeId)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = filter_var($_POST['name'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);

            $errors = [];

            if (empty($name)) {
                $errors['name'] = 'Le nom est requis.';
            } elseif (strlen($name) > 50) {
                $errors['name'] = 'Le nom ne doit pas dépasser 50 caractères.';
            }

            if (!empty($errors)) {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'Veuillez corriger les erreurs dans le formulaire.',
                    'context' => 'modal',
                    'errors' => $errors
                ];
                header('Location: /dashboard/roomtypes/edit/' . $roomTypeId);
                exit;
            }

            $roomType = $this->typeModel->getTypeById((int) $roomTypeId);
            if (!$roomType) {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'Le type de chambre n\'existe pas.',
                    'context' => 'global'
                ];
                header('Location: /dashboard/roomtypes');
                exit;
            }

            $roomType->setName($name);
            // Set updated timestamp if the entity supports it
            if (method_exists($roomType, 'setUpdatedAt')) {
                $roomType->setUpdatedAt(new \DateTime());
            }

            $this->typeModel->updateType($roomType);

            $_SESSION['alert'] = [
                'status' => 'success',
                'message' => 'Le type de chambre a été mis à jour avec succès.',
                'context' => 'global'
            ];
            header('Location: /dashboard/roomtypes');
            exit;
        }

        $roomType = $this->typeModel->getTypeById((int) $roomTypeId);
        if (!$roomType) {
            $_SESSION['alert'] = [
                'status' => 'error',
                'message' => 'Le type de chambre n\'existe pas.',
                'context' => 'global'
            ];
            header('Location: /dashboard/roomtypes');
            exit;
        }

        echo $this->twig->render('roomTypeController/editRoomType.html.twig', [
            'roomType' => $roomType
        ]);
    }
}
