<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\DependencyContainer;
use Twig\Environment;
use App\Entity\Room;
use App\Entity\Type;

class RoomController
{
    private $twig;
    private $roomModel;
    private $typeModel;
    private $imageModel;
    private $equipmentModel;

    public function __construct(Environment $twig, DependencyContainer $dependencyContainer)
    {
        $this->twig = $twig;
        $this->roomModel = $dependencyContainer->get('RoomModel');
        $this->typeModel = $dependencyContainer->get('TypeModel');
        $this->imageModel = $dependencyContainer->get('ImageModel');
        $this->equipmentModel = $dependencyContainer->get('EquipmentModel');
    }
    // Affiche la liste des chambres disponibles
    public function searchRooms()
    {

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $arrivalDateStr = filter_input(INPUT_POST, 'arrival_date', FILTER_SANITIZE_SPECIAL_CHARS) ?? '';
            $departureDateStr = filter_input(INPUT_POST, 'departure_date', FILTER_SANITIZE_SPECIAL_CHARS) ?? '';
            $arrivalDate = !empty($arrivalDateStr) ? new \DateTime($arrivalDateStr) : new \DateTime();
            $departureDate = !empty($departureDateStr) ? new \DateTime($departureDateStr) : new \DateTime('+1 day');
            $numberOfGuests = filter_input(INPUT_POST, 'number_of_guests', FILTER_SANITIZE_NUMBER_INT) ?? '';

            $sortBy = filter_input(INPUT_POST, 'sort_by', FILTER_SANITIZE_SPECIAL_CHARS) ?? 'price';
            $sortOrder = filter_input(INPUT_POST, 'sort_order', FILTER_SANITIZE_SPECIAL_CHARS) ?? 'asc';
            $equipmentIds = isset($_POST['equipment_ids']) ? (array)$_POST['equipment_ids'] : [];
            $typeIds = isset($_POST['type_ids']) ? (array)$_POST['type_ids'] : [];
            $minPrice = filter_input(INPUT_POST, 'min_price', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) ?? null;
            $maxPrice = filter_input(INPUT_POST, 'max_price', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) ?? null;

            $rooms = $this->roomModel->getAvailableRooms($arrivalDate, $departureDate, (int)$numberOfGuests);

            $filteredRooms = [];
            foreach ($rooms as $room) {

                if ($minPrice !== null && $room->getPrice() < (float)$minPrice) {
                    continue;
                }
                if ($maxPrice !== null && $room->getPrice() > (float)$maxPrice) {
                    continue;
                }

                if (!empty($typeIds)) {
                    $roomTypeId = $room->getType()->getId();
                    if (!in_array((int)$roomTypeId, $typeIds)) {
                        continue;
                    }
                }

                if (!empty($equipmentIds)) {
                    $roomEquipmentIds = array_map(function ($equipment) {
                        return $equipment->getId();
                    }, $room->equipments);

                    $hasAnyEquipment = false;
                    foreach ($equipmentIds as $eqId) {
                        if (in_array((int)$eqId, $roomEquipmentIds)) {
                            $hasAnyEquipment = true;
                            break;
                        }
                    }

                    if (!$hasAnyEquipment) {
                        continue;
                    }
                }

                $filteredRooms[] = $room;
            }

            usort($filteredRooms, function ($a, $b) use ($sortBy, $sortOrder) {
                $aValue = 0;
                $bValue = 0;

                switch ($sortBy) {
                    case 'price':
                        $aValue = $a->getPrice() - $a->getDiscount();
                        $bValue = $b->getPrice() - $b->getDiscount();
                        break;
                    case 'capacity':
                        $aValue = $a->getCapacity();
                        $bValue = $b->getCapacity();
                        break;
                    case 'name':
                        return $sortOrder === 'asc' ?
                            strcmp($a->getName(), $b->getName()) :
                            strcmp($b->getName(), $a->getName());
                    default:
                        $aValue = $a->getPrice();
                        $bValue = $b->getPrice();
                }

                return $sortOrder === 'asc' ? $aValue - $bValue : $bValue - $aValue;
            });

            $roomsArray = [];
            foreach ($filteredRooms as $room) {
                $roomData = [
                    'id' => $room->getId(),
                    'name' => $room->getName(),
                    'description' => $room->getDescription(),
                    'bedSingle' => $room->getBedSingle(),
                    'bedDouble' => $room->getBedDouble(),
                    'capacity' => $room->getCapacity(),
                    'available' => $room->isAvailable(),
                    'price' => $room->getPrice(),
                    'discount' => $room->getDiscount(),
                    'createdAt' => $room->getCreatedAt()->format('Y-m-d H:i:s'),
                    'updatedAt' => $room->getUpdatedAt()->format('Y-m-d H:i:s'),
                ];

                $type = $room->getType();
                $roomData['type'] = [
                    'id' => $type->getId(),
                    'name' => $type->getName(),
                ];

                $roomData['images'] = [];
                foreach ($room->images as $image) {
                    $roomData['images'][] = [
                        'id' => $image->getId(),
                        'fileKey' => $image->getFileKey(),
                        'name' => $image->getName(),
                        'extension' => $image->getExtension(),
                        'description' => $image->getDescription(),
                        'createdAt' => $image->getCreatedAt()->format('Y-m-d H:i:s'),
                        'updatedAt' => $image->getUpdatedAt()->format('Y-m-d H:i:s'),
                    ];
                }

                $roomData['equipments'] = [];
                foreach ($room->equipments as $equipment) {
                    $roomData['equipments'][] = [
                        'id' => $equipment->getId(),
                        'name' => $equipment->getName(),
                        'icon' => $equipment->getIcon(),
                    ];
                }

                $roomsArray[] = $roomData;
            }

            $json = json_encode($roomsArray, JSON_PRETTY_PRINT);
            header('Content-Type: application/json');
            echo $json;
            exit;
        }

        $arrivalDateStr = filter_input(INPUT_GET, 'arrival_date', FILTER_SANITIZE_SPECIAL_CHARS) ?? '';
        $departureDateStr = filter_input(INPUT_GET, 'departure_date', FILTER_SANITIZE_SPECIAL_CHARS) ?? '';
        $numberOfGuests = filter_input(INPUT_GET, 'number_of_guests', FILTER_SANITIZE_NUMBER_INT) ?? '';

        $equipments = $this->equipmentModel->getAllEquipments();
        $types = $this->typeModel->getAllTypes();



        echo $this->twig->render('roomController/search.html.twig', [
            'arrival_date' => $arrivalDateStr,
            'departure_date' => $departureDateStr,
            'number_of_guests' => $numberOfGuests,
            'equipments' => $equipments,
            'roomTypes' => $types,
        ]);
    }

    public function room(int $roomId)
    {
        $arrivalDate = filter_input(INPUT_GET, 'arrival_date', FILTER_SANITIZE_SPECIAL_CHARS) ?? '';
        $departureDate = filter_input(INPUT_GET, 'departure_date', FILTER_SANITIZE_SPECIAL_CHARS) ?? '';
        $numberOfGuests = filter_input(INPUT_GET, 'number_of_guests', FILTER_SANITIZE_NUMBER_INT) ?? '';

        $room = $this->roomModel->getRoomById((int) $roomId);
        if (!$room) {
            $_SESSION['alert'] = [
                'status' => 'error',
                'message' => 'La chambre n\'existe pas.',
                'context' => 'global'
            ];
            header('Location: /search');
            exit;
        }

        // On applique les sécurités seulement si toutes les données sont définies
        if (!empty($arrivalDate) && !empty($departureDate) && !empty($numberOfGuests)) {

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
        }

        echo $this->twig->render('roomController/room.html.twig', [
            'room' => $room,
            'arrival_date' => $arrivalDate,
            'departure_date' => $departureDate,
            'number_of_guests' => $numberOfGuests,
        ]);
    }

    public function rooms()
    {
        $rooms = $this->roomModel->getAllRooms();
        $types = $this->typeModel->getAllTypes();
        $images = $this->imageModel->getAllImages();
        $equipments = $this->equipmentModel->getAllEquipments();

        echo $this->twig->render('roomController/rooms.html.twig', [
            'rooms' => $rooms,
            'types' => $types,
            'images' => $images,
            'equipments' => $equipments
        ]);
    }

    public function addRoom()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $name = filter_var($_POST['name'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
            $description = filter_var($_POST['description'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
            $available = (bool)($_POST['available'] ?? 0);
            $typeId = filter_var($_POST['type_id'] ?? 0, FILTER_SANITIZE_NUMBER_INT);
            $price = filter_var($_POST['price'] ?? 0, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            $discount = filter_var($_POST['discount'] ?? 0, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            $bedSingle = filter_var($_POST['bed_single'] ?? 0, FILTER_SANITIZE_NUMBER_INT);
            $bedDouble = filter_var($_POST['bed_double'] ?? 0, FILTER_SANITIZE_NUMBER_INT);
            $capacity = filter_var($_POST['capacity'] ?? 0, FILTER_SANITIZE_NUMBER_INT);
            $imageIds = $_POST['images'] ?? [];
            $equipmentIds = $_POST['equipments'] ?? [];

            $errors = [];

            if (empty($name)) {
                $errors['name'] = 'Le nom est requis.';
            } elseif (strlen($name) > 50) {
                $errors['name'] = 'Le nom ne doit pas dépasser 50 caractères.';
            }

            if (empty($typeId)) {
                $errors['type_id'] = 'Le type de chambre est requis.';
            }

            if (!is_numeric($price) || $price <= 0) {
                $errors['price'] = 'Le prix doit être un nombre positif.';
            }

            if (!is_numeric($discount) || $discount < 0) {
                $errors['discount'] = 'La réduction doit être un nombre positif ou zéro.';
            }

            if (!is_numeric($bedSingle) || $bedSingle < 0) {
                $errors['bed_single'] = 'Le nombre de lits simples doit être un nombre positif ou zéro.';
            }

            if (!is_numeric($bedDouble) || $bedDouble < 0) {
                $errors['bed_double'] = 'Le nombre de lits doubles doit être un nombre positif ou zéro.';
            }

            if (!is_numeric($capacity) || $capacity <= 0) {
                $errors['capacity'] = 'La capacité doit être un nombre positif.';
            }

            if (empty($imageIds)) {
                $errors['images'] = 'Au moins une image est requise.';
            }

            if (!empty($errors)) {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'Veuillez corriger les erreurs dans le formulaire.',
                    'context' => 'modal',
                    'errors' => $errors
                ];
                header('Location: /dashboard/rooms?modal=create-room');
                exit;
            }

            $type = $this->typeModel->getTypeById((int)$typeId);
            if (!$type) {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'Le type de chambre sélectionné n\'existe pas.',
                    'context' => 'modal',
                    'errors' => ['type_id' => 'Type de chambre invalide.']
                ];
                header('Location: /dashboard/rooms?modal=create-room');
                exit;
            }

            $images = [];
            foreach ($imageIds as $imageId) {
                $image = $this->imageModel->getImageById($imageId);
                if ($image) {
                    $images[] = $image;
                }
            }

            $equipments = [];
            foreach ($equipmentIds as $equipmentId) {
                $equipment = $this->equipmentModel->getEquipmentById((int)$equipmentId);
                if ($equipment) {
                    $equipments[] = $equipment;
                }
            }

            $room = new Room(
                null,
                $name,
                $description,
                (int)$bedSingle,
                (int)$bedDouble,
                (int)$capacity,
                $available,
                $type,
                (float)$price,
                (float)$discount,
                new \DateTime(),
                new \DateTime()
            );

            try {
                $this->roomModel->createRoom($room, $images, $equipments);
                $_SESSION['alert'] = [
                    'status' => 'success',
                    'message' => 'La chambre a été créée avec succès.',
                    'context' => 'global'
                ];
                header('Location: /dashboard/rooms');
                exit;
            } catch (\Exception $e) {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'Une erreur est survenue lors de la création de la chambre: ' . $e->getMessage(),
                    'context' => 'global'
                ];
                header('Location: /dashboard/rooms');
                exit;
            }
        }

        header('Location: /dashboard/rooms');
        exit;
    }

    public function editRoom(int $room_id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = filter_var($_POST['name'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
            $description = filter_var($_POST['description'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
            $available = (bool)($_POST['available'] ?? 0);
            $typeId = filter_var($_POST['type_id'] ?? 0, FILTER_SANITIZE_NUMBER_INT);
            $price = filter_var($_POST['price'] ?? 0, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            $discount = filter_var($_POST['discount'] ?? 0, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            $bedSingle = filter_var($_POST['bed_single'] ?? 0, FILTER_SANITIZE_NUMBER_INT);
            $bedDouble = filter_var($_POST['bed_double'] ?? 0, FILTER_SANITIZE_NUMBER_INT);
            $capacity = filter_var($_POST['capacity'] ?? 0, FILTER_SANITIZE_NUMBER_INT);
            $imageIds = $_POST['images'] ?? [];
            $equipmentIds = $_POST['equipments'] ?? [];

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
                header('Location: /dashboard/rooms/edit/' . $room_id);
                exit;
            }

            $room = $this->roomModel->getRoomById($room_id);
            if (!$room) {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'La chambre n\'existe pas.',
                    'context' => 'global'
                ];
                header('Location: /dashboard/rooms');
                exit;
            }

            $type = $this->typeModel->getTypeById((int)$typeId);
            if (!$type) {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'Le type de chambre sélectionné n\'existe pas.',
                    'context' => 'global'
                ];
                header('Location: /dashboard/rooms/edit/' . $room_id);
                exit;
            }

            $images = [];
            foreach ($imageIds as $imageId) {
                $image = $this->imageModel->getImageById($imageId);
                if ($image) {
                    $images[] = $image;
                }
            }

            $equipments = [];
            foreach ($equipmentIds as $equipmentId) {
                $equipment = $this->equipmentModel->getEquipmentById((int)$equipmentId);
                if ($equipment) {
                    $equipments[] = $equipment;
                }
            }

            $room->setName($name);
            $room->setDescription($description);
            $room->setBedSingle((int)$bedSingle);
            $room->setBedDouble((int)$bedDouble);
            $room->setCapacity((int)$capacity);
            $room->setAvailable($available);
            $room->setType($type);
            $room->setPrice((float)$price);
            $room->setDiscount((float)$discount);
            $room->setUpdatedAt(new \DateTime());

            try {
                $this->roomModel->updateRoom($room, $images, $equipments);
                $_SESSION['alert'] = [
                    'status' => 'success',
                    'message' => 'La chambre a été mise à jour avec succès.',
                    'context' => 'global'
                ];
                header('Location: /dashboard/rooms');
                exit;
            } catch (\Exception $e) {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'Une erreur est survenue lors de la mise à jour de la chambre: ' . $e->getMessage(),
                    'context' => 'global'
                ];
                header('Location: /dashboard/rooms');
                exit;
            }
        }

        $room = $this->roomModel->getRoomById($room_id);
        if (!$room) {
            $_SESSION['alert'] = [
                'status' => 'error',
                'message' => 'La chambre n\'existe pas.',
                'context' => 'global'
            ];
            header('Location: /dashboard/rooms');
            exit;
        }

        $types = $this->typeModel->getAllTypes();
        $images = $this->imageModel->getAllImages();
        $equipments = $this->equipmentModel->getAllEquipments();



        echo $this->twig->render('roomController/editRoom.html.twig', [
            'room' => $room,
            'types' => $types,
            'images' => $images,
            'equipments' => $equipments
        ]);
    }

    public function deleteRoom(int $room_id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $room = $this->roomModel->getRoomById($room_id);

            if (!$room) {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'La chambre n\'existe pas.',
                    'context' => 'global'
                ];
                header('Location: /dashboard/rooms');
                exit;
            }

            try {
                $this->roomModel->deleteRoom($room_id);
                $_SESSION['alert'] = [
                    'status' => 'success',
                    'message' => 'La chambre a été supprimée avec succès.',
                    'context' => 'global'
                ];
            } catch (\Exception $e) {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'Une erreur est survenue lors de la suppression de la chambre: ' . $e->getMessage(),
                    'context' => 'global'
                ];
            }

            header('Location: /dashboard/rooms');
            exit;
        }

        header('Location: /dashboard/rooms');
        exit;
    }
}
