<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\DependencyContainer;
use Twig\Environment;

use App\Entity\Equipment;


class EquipmentController
{
    private $twig;
    private $equipmentModel;

    public function __construct(Environment $twig, DependencyContainer $dependencyContainer)
    {
        $this->twig = $twig;
        $this->equipmentModel = $dependencyContainer->get('EquipmentModel');
    }

    public function equipments()
    {
        $equipments = $this->equipmentModel->getAllEquipments();
        echo $this->twig->render('equipmentController/equipments.html.twig', [
            'equipments' => $equipments
        ]);
    }

    public function addEquipment()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = filter_var($_POST['name'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
            $icon = filter_var($_POST['icon'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);

            $errors = [];

            if (empty($name)) {
                $errors['name'] = 'Le nom est requis.';
            } elseif (strlen($name) > 50) {
                $errors['name'] = 'Le nom ne doit pas dépasser 50 caractères.';
            }

            if (strlen($icon) > 20) {
                $errors['icon'] = 'L\'icône ne doit pas dépasser 20 caractères.';
            }

            if (!empty($errors)) {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'Veuillez corriger les erreurs dans le formulaire.',
                    'context' => 'modal',
                    'errors' => $errors
                ];
                header('Location: /dashboard/equipment?modal=create-equipment');
                exit;
            }

            $equipment = new Equipment(
                null,
                $name,
                $icon,
                new \DateTime(),
                new \DateTime(),
            );

            if (empty($errors)) {
                $this->equipmentModel->createEquipment($equipment);
                header('Location: /dashboard/equipments');
                exit;
            }

            $_SESSION['alert'] = [
                'status' => 'success',
                'message' => 'L\'équipement a été créé avec succès.',
                'context' => 'global'
            ];
            header('Location: /dashboard/equipments');
            exit;
        }

        header('Location: /dashboard/equipments');
        exit;
    }

    public function deleteEquipment(int $equipment_id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $equipment = $this->equipmentModel->getEquipmentById((int) $equipment_id);

            if (!$equipment) {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'L\'équipement n\'existe pas.',
                    'context' => 'global'
                ];
                header('Location: /dashboard/equipments');
                exit;
            }

            $this->equipmentModel->deleteEquipment((int) $equipment_id);
            $_SESSION['alert'] = [
                'status' => 'success',
                'message' => 'L\'équipement a été supprimé avec succès.',
                'context' => 'global'
            ];
            header('Location: /dashboard/equipments');
            exit;
        }

        header('Location: /dashboard/equipments');
        exit;
    }

    public function editEquipment(int $equipment_id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = filter_var($_POST['name'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
            $icon = filter_var($_POST['icon'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);

            $errors = [];

            if (empty($name)) {
                $errors['name'] = 'Le nom est requis.';
            } elseif (strlen($name) > 50) {
                $errors['name'] = 'Le nom ne doit pas dépasser 50 caractères.';
            }

            if (strlen($icon) > 20) {
                $errors['icon'] = 'L\'icône ne doit pas dépasser 20 caractères.';
            }

            if (!empty($errors)) {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'Veuillez corriger les erreurs dans le formulaire.',
                    'context' => 'modal',
                    'errors' => $errors
                ];
                header('Location: /dashboard/equipments/edit/' . $equipment_id);
                exit;
            }

            $equipment = $this->equipmentModel->getEquipmentById((int) $equipment_id);
            if (!$equipment) {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'L\'équipement n\'existe pas.',
                    'context' => 'global'
                ];
                header('Location: /dashboard/equipments');
                exit;
            }

            $equipment->setName($name);
            $equipment->setIcon($icon);
            $equipment->setUpdatedAt(new \DateTime());

            $this->equipmentModel->updateEquipment($equipment);

            $_SESSION['alert'] = [
                'status' => 'success',
                'message' => 'L\'équipement a été mis à jour avec succès.',
                'context' => 'global'
            ];
            header('Location: /dashboard/equipments');
            exit;
        }

        $equipment = $this->equipmentModel->getEquipmentById((int) $equipment_id);
        if (!$equipment) {
            $_SESSION['alert'] = [
                'status' => 'error',
                'message' => 'L\'équipement n\'existe pas.',
                'context' => 'global'
            ];
            header('Location: /dashboard/equipments');
            exit;
        }

        echo $this->twig->render('equipmentController/editEquipment.html.twig', [
            'equipment' => $equipment
        ]);
    }
}
