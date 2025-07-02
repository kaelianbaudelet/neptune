<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\DependencyContainer;
use Twig\Environment;

use App\Entity\Image;

class ImageController
{
    private $twig;
    private $imageModel;

    public function __construct(Environment $twig, DependencyContainer $dependencyContainer)
    {
        $this->twig = $twig;
        $this->imageModel = $dependencyContainer->get('ImageModel');
    }

    public function images()
    {
        $images = $this->imageModel->getAllImages();
        echo $this->twig->render('imageController/images.html.twig', [
            'images' => $images
        ]);
    }

    public function addImage()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $imageFile = $_FILES['image'] ?? null;
            $name = filter_var($_POST['name'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
            $description = filter_var($_POST['description'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);

            $errors = [];

            if (empty($name)) {
                $errors['name'] = 'Le nom est requis.';
            } elseif (strlen($name) < 10) {
                $errors['name'] = 'Le nom doit contenir au moins 10 caractères.';
            } elseif (strlen($name) > 50) {
                $errors['name'] = 'Le nom ne doit pas dépasser 50 caractères.';
            }

            if (strlen($description) > 180) {
                $errors['description'] = 'La description ne doit pas dépasser 180 caractères.';
            }

            if (!$imageFile || $imageFile['error'] !== UPLOAD_ERR_OK) {
                $errors['image'] = 'Le fichier est requis.';
            } else {
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

                $fileType = mime_content_type($imageFile['tmp_name']);
                $fileExtension = strtolower(pathinfo($imageFile['name'], PATHINFO_EXTENSION));

                if (!in_array($fileType, $allowedTypes) || !in_array($fileExtension, $allowedExtensions)) {
                    $errors['image'] = 'Le fichier doit être une image valide (jpg, jpeg, png, gif, webp).';
                }
            }

            if (!empty($errors)) {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'Veuillez corriger les erreurs dans le formulaire.',
                    'context' => 'modal',
                    'errors' => $errors
                ];
                header('Location: /dashboard/images?modal=upload-image');
                exit;
            }

            $maxFileSize = $_ENV['UPLOAD_MAX_FILESIZE'] ?? 2097152;
            if ($imageFile['size'] > $maxFileSize) {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => "Le fichier est trop volumineux. La taille maximale autorisée est de " . round($maxFileSize / (1024 * 1024), 2) . " MB.",
                    'context' => 'modal'
                ];
                header('Location: /dashboard/images?modal=upload-image');
                exit;
            }

            // Process the file upload
            $fileKey = pathinfo($imageFile['name'], PATHINFO_FILENAME);
            $uploadDir = __DIR__ . '/../../public/assets/upload/';
            $fileExtension = pathinfo($imageFile['name'], PATHINFO_EXTENSION);
            $fileName = $fileKey . '.' . $fileExtension;
            $uploadFile = $uploadDir . $fileName;

            if (file_exists($uploadFile)) {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => "Une image {$fileKey}.{$fileExtension} existe déjà. Supprimez l'image existante ou choisissez un autre nom de fichier pour cette image.",
                    'context' => 'global'
                ];
                header('Location: /dashboard/images');
                exit;
            }

            if (!move_uploaded_file($imageFile['tmp_name'], $uploadFile)) {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'Impossible de déplacer le fichier téléchargé.',
                    'context' => 'global'
                ];
                header('Location: /dashboard/images');
                exit;
            }

            $image = new Image(
                null,
                $fileKey,
                $name,
                $fileExtension,
                $description,
                new \DateTime(),
                new \DateTime(),
                $fileName
            );
            $this->imageModel->createImage($image);

            $_SESSION['alert'] = [
                'status' => 'success',
                'message' => 'L\'image a été téléchargée avec succès.',
                'context' => 'global'
            ];
            header('Location: /dashboard/images');
            exit;
        }

        header('Location: /dashboard/images');
        exit;
    }

    public function downloadImage($image_id)
    {
        $image = $this->imageModel->getImageById($image_id);

        if (!$image) {
            $_SESSION['alert'] = [
                'status' => 'error',
                'message' => 'L\'image demandée n\'existe pas.',
                'context' => 'global'
            ];
            header('Location: /dashboard/images');
            exit;
        }

        $filePath = __DIR__ . '/../../public/assets/upload/' . $image->getFileKey() . '.' . $image->getExtension();

        if (!file_exists($filePath)) {
            $_SESSION['alert'] = [
                'status' => 'error',
                'message' => 'Le fichier demandé n\'existe pas.',
                'context' => 'global'
            ];
            header('Location: /dashboard/images');
            exit;
        }

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
        readfile($filePath);
        exit;
    }

    public function deleteImage($image_id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $image = $this->imageModel->getImageById((string) $image_id);

            if (!$image) {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'L\'image demandée n\'existe pas.',
                    'context' => 'global'
                ];
                header('Location: /dashboard/images');
                exit;
            }

            $filePath = __DIR__ . '/../../public/assets/upload/' . $image->getFileKey() . '.' . $image->getExtension();

            if (file_exists($filePath)) {
                unlink($filePath);
            }

            $this->imageModel->deleteImage((string) $image_id);

            $_SESSION['alert'] = [
                'status' => 'success',
                'message' => 'L\'image a été supprimée avec succès.',
                'context' => 'global'
            ];
            header('Location: /dashboard/images');
            exit;
        }

        header('Location: /dashboard/images');
        exit;
    }

    public function editImage($image_id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = filter_var($_POST['name'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
            $description = filter_var($_POST['description'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);

            $errors = [];

            if (empty($name)) {
                $errors['name'] = 'Le nom est requis.';
            } elseif (strlen($name) < 10) {
                $errors['name'] = 'Le nom doit contenir au moins 10 caractères.';
            } elseif (strlen($name) > 50) {
                $errors['name'] = 'Le nom ne doit pas dépasser 50 caractères.';
            }

            if (strlen($description) > 180) {
                $errors['description'] = 'La description ne doit pas dépasser 180 caractères.';
            }

            if (!empty($errors)) {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'Veuillez corriger les erreurs dans le formulaire.',
                    'context' => 'modal',
                    'errors' => $errors
                ];
                header('Location: /dashboard/images/edit/' . $image_id);
                exit;
            }

            $image = $this->imageModel->getImageById((string) $image_id);
            if (!$image) {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'L\'image n\'existe pas.',
                    'context' => 'global'
                ];
                header('Location: /dashboard/images');
                exit;
            }

            $image->setName($name);
            $image->setDescription($description);
            $image->setUpdatedAt(new \DateTime());

            $this->imageModel->updateImage($image);

            $_SESSION['alert'] = [
                'status' => 'success',
                'message' => 'L\'image a été mise à jour avec succès.',
                'context' => 'global'
            ];
            header('Location: /dashboard/images');
            exit;
        }

        $image = $this->imageModel->getImageById($image_id);
        if (!$image) {
            $_SESSION['alert'] = [
                'status' => 'error',
                'message' => 'L\'image demandée n\'existe pas.',
                'context' => 'global'
            ];
            header('Location: /dashboard/images');
            exit;
        }

        echo $this->twig->render('imageController/editImage.html.twig', [
            'image' => $image
        ]);
    }
}
