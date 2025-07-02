<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\DependencyContainer;
use Twig\Environment;

use App\Entity\User;

class UserController
{
    private $twig;
    private $userModel;

    public function __construct(Environment $twig, DependencyContainer $dependencyContainer)
    {
        $this->twig = $twig;
        $this->userModel = $dependencyContainer->get('UserModel');
    }

    public function users()
    {
        $users = $this->userModel->getAllUsers();
        echo $this->twig->render('userController/users.html.twig', [
            'users' => $users
        ]);
    }

    public function addUser()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = filter_var($_POST['name'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
            $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
            $type = filter_var($_POST['type'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
            $password = $_POST['password'] ?? '';
            $role = filter_var($_POST['role'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);

            $errors = [];

            if (empty($name)) {
                $errors['name'] = 'Le nom est requis.';
            } elseif (strlen($name) > 50) {
                $errors['name'] = 'Le nom ne doit pas dépasser 50 caractères.';
            }

            if (empty($email)) {
                $errors['email'] = 'L\'email est requis.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'L\'email n\'est pas valide.';
            }

            if (empty($password)) {
                $errors['password'] = 'Le mot de passe est requis.';
            } elseif (strlen($password) < 8) {
                $errors['password'] = 'Le mot de passe doit contenir au moins 8 caractères.';
            }

            if (empty($type)) {
                $errors['type'] = 'Le type est requis.';
            }

            if ($type !== 'individual' && $type !== 'company') {
                $errors['type'] = 'Le type est invalide.';
            }

            if (empty($role)) {
                $errors['role'] = 'Le rôle est requis.';
            }

            if (!empty($errors)) {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'Veuillez corriger les erreurs dans le formulaire.',
                    'context' => 'modal',
                    'errors' => $errors
                ];
                header('Location: /dashboard/users?modal=create-user');
                exit;
            }

            $user = new User(
                null,
                $name,
                $email,
                null,
                false,
                $type,
                $password,
                false,
                null,
                null,
                null,
                null,
                $role,
                new \DateTime(),
                new \DateTime()
            );

            $this->userModel->createUser($user);

            $_SESSION['alert'] = [
                'status' => 'success',
                'message' => 'L\'utilisateur a été créé avec succès.',
                'context' => 'global'
            ];
            header('Location: /dashboard/users');
            exit;
        }

        header('Location: /dashboard/users');
        exit;
    }

    public function deleteUser(string $user_id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user = $this->userModel->getUserById((string) $user_id);

            if (!$user) {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'L\'utilisateur n\'existe pas.',
                    'context' => 'global'
                ];
                header('Location: /dashboard/users');
                exit;
            }

            $this->userModel->deleteUser((string) $user_id);
            $_SESSION['alert'] = [
                'status' => 'success',
                'message' => 'L\'utilisateur a été supprimé avec succès.',
                'context' => 'global'
            ];
            header('Location: /dashboard/users');
            exit;
        }

        header('Location: /dashboard/users');
        exit;
    }

    public function editUser(string $user_id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = filter_var($_POST['name'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
            $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
            $type = filter_var($_POST['type'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
            $role = filter_var($_POST['role'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
            $password = $_POST['password'] ?? '';

            $errors = [];

            if (empty($name)) {
                $errors['name'] = 'Le nom est requis.';
            } elseif (strlen($name) > 50) {
                $errors['name'] = 'Le nom ne doit pas dépasser 50 caractères.';
            }

            if (empty($email)) {
                $errors['email'] = 'L\'email est requis.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'L\'email n\'est pas valide.';
            }

            if (!empty($password) && strlen($password) < 8) {
                $errors['password'] = 'Le mot de passe doit contenir au moins 8 caractères.';
            }

            if (empty($type)) {
                $errors['type'] = 'Le type est requis.';
            }

            if (empty($role)) {
                $errors['role'] = 'Le rôle est requis.';
            }

            if (!empty($errors)) {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'Veuillez corriger les erreurs dans le formulaire.',
                    'context' => 'modal',
                    'errors' => $errors
                ];
                header('Location: /dashboard/users/edit/' . $user_id);
                exit;
            }

            $user = $this->userModel->getUserById((string) $user_id);
            if (!$user) {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'L\'utilisateur n\'existe pas.',
                    'context' => 'global'
                ];
                header('Location: /dashboard/users');
                exit;
            }

            $user->setName($name);
            $user->setEmail($email);
            $user->setType($type);
            $user->setRole($role);
            $user->setUpdatedAt(new \DateTime());

            if (!empty($password)) {
                $user->setPassword($password);
            }

            $this->userModel->updateUser($user);

            $_SESSION['alert'] = [
                'status' => 'success',
                'message' => 'L\'utilisateur a été mis à jour avec succès.',
                'context' => 'global'
            ];
            header('Location: /dashboard/users');
            exit;
        }

        $user = $this->userModel->getUserById((string) $user_id);
        if (!$user) {
            $_SESSION['alert'] = [
                'status' => 'error',
                'message' => 'L\'utilisateur n\'existe pas.',
                'context' => 'global'
            ];
            header('Location: /dashboard/users');
            exit;
        }

        echo $this->twig->render('userController/editUser.html.twig', [
            'user' => $user
        ]);
    }
}
