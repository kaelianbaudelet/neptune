<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\DependencyContainer;
use Twig\Environment;
use App\Model\UserModel;
use App\Entity\User;
use App\Model\BookingModel;
use App\Entity\BillingAddress;

class ProfileController
{
    private $twig;
    private $userModel;
    private $bookingModel;
    private $billingAddressModel;

    public function __construct(Environment $twig, DependencyContainer $dependencyContainer)
    {
        $this->twig = $twig;
        $this->userModel = $dependencyContainer->get('UserModel');
        $this->bookingModel = $dependencyContainer->get('BookingModel');
        $this->billingAddressModel = $dependencyContainer->get('BillingAddressModel');
    }

    public function profile()
    {
        // Vérifier que l'utilisateur est connecté
        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }

        $userId = $_SESSION['user']['id'];
        $user = $this->userModel->getUserById($userId);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Récupérer et filtrer les données du formulaire
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $phone = trim($_POST['phone'] ?? '');

            $errors = [];

            // Validation du nom
            if (empty($name)) {
                $errors['name'] = 'Le nom complet est requis.';
            } elseif (mb_strlen($name) > 100) {
                $errors['name'] = 'Le nom ne doit pas dépasser 100 caractères.';
            }

            // Validation de l'email
            if (empty($email)) {
                $errors['email'] = 'L\'adresse e-mail est requise.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'L\'adresse e-mail n\'est pas valide.';
            }

            // Validation du téléphone (optionnel, mais on peut vérifier le format FR)
            if (!empty($phone) && !preg_match('/^0[1-9](\s?\d{2}){4}$/', $phone)) {
                $errors['phone'] = 'Le numéro de téléphone n\'est pas valide.';
            }

            // Vérifier si l'email est déjà utilisé par un autre utilisateur
            $existingUser = $this->userModel->getUserByEmail($email);
            if ($existingUser && $existingUser->getId() !== $user->getId()) {
                $errors['email'] = 'Cette adresse e-mail est déjà utilisée par un autre compte.';
            }

            if (!empty($errors)) {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'Veuillez corriger les erreurs dans le formulaire.',
                    'errors' => $errors
                ];
                // On renvoie les anciennes valeurs pour pré-remplir le formulaire
                echo $this->twig->render('profileController/profile.html.twig', [
                    'user' => $user,
                    'old' => [
                        'name' => $name,
                        'email' => $email,
                        'phone' => $phone
                    ],
                    'errors' => $errors
                ]);
                return;
            }

            // Mettre à jour l'utilisateur
            $user->setName($name);
            $user->setEmail($email);
            $user->setPhone($phone);
            $user->setUpdatedAt(new \DateTime());

            $this->userModel->updateUser($user);

            $_SESSION['alert'] = [
                'status' => 'success',
                'message' => 'Votre profil a été mis à jour avec succès.'
            ];

            // Mettre à jour la session si l'email ou le nom a changé
            $_SESSION['user']['name'] = $user->getName();
            $_SESSION['user']['email'] = $user->getEmail();

            header('Location: /dashboard/profile');
            exit;
        }

        // GET : afficher le formulaire avec les infos actuelles
        echo $this->twig->render('profileController/profile.html.twig', [
            'user' => $user
        ]);
    }

    public function password()
    {
        // Vérification que l'utilisateur est connecté
        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }

        $errors = [];

        // Si le formulaire est soumis en POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmNewPassword = $_POST['confirm_new_password'] ?? '';

            // Récupérer l'utilisateur courant depuis la base de données
            $user = $this->userModel->getUserById($_SESSION['user']['id']);

            // Vérification du mot de passe actuel
            if (empty($currentPassword) || !password_verify($currentPassword, $user->getPassword())) {
                $errors['current_password'] = "Le mot de passe actuel est incorrect.";
            }

            // Vérification du nouveau mot de passe
            if (empty($newPassword)) {
                $errors['new_password'] = "Veuillez saisir un nouveau mot de passe.";
            } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]).{8,50}$/', $newPassword)) {
                $errors['new_password'] = "Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial.";
            }

            // Vérification de la confirmation
            if ($newPassword !== $confirmNewPassword) {
                $errors['confirm_new_password'] = "Les mots de passe ne correspondent pas.";
            }

            if (empty($errors)) {

                if (password_verify($newPassword, $user->getPassword())) {
                    $errors['new_password'] = "Le nouveau mot de passe ne peut pas être le même que l'ancien.";
                } else {
                    $user->setPassword($newPassword); // hash coté model
                    $user->setUpdatedAt(new \DateTime());
                    $this->userModel->updateUser($user);

                    // Supprimer la session pour forcer la reconnexion
                    session_destroy();

                    $_SESSION['alert'] = [
                        'status' => 'success',
                        'message' => 'Votre mot de passe a été modifié avec succès. Veuillez vous reconnecter.'
                    ];

                    // Rediriger vers la page de connexion pour voir l'alerte
                    header('Location: /login');
                    exit;
                }
            }

            if (!empty($errors)) {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'Veuillez corriger les erreurs dans le formulaire.',
                    'errors' => $errors
                ];
            }
        }

        // Affichage du formulaire avec les éventuelles erreurs
        echo $this->twig->render('profileController/password.html.twig', [
            'errors' => $errors
        ]);
    }

    public function deleteAccount()
    {
        // Vérifier que l'utilisateur est connecté
        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }

        $user = $this->userModel->getUserById($_SESSION['user']['id']);

        // Vérifier s'il y a des réservations en cours pour cet utilisateur
        $hasActiveBookings = false;
        if (method_exists($this->bookingModel, 'getAllBookings')) {
            $bookings = $this->bookingModel->getAllBookings();
            foreach ($bookings as $booking) {
                if (
                    $booking->getUser() &&
                    $booking->getUser()->getId() === $user->getId() &&
                    (
                        // On considère qu'une réservation est "en cours" si la date de checkout est dans le futur
                        $booking->getCheckout() > new \DateTime()
                    )
                ) {
                    $hasActiveBookings = true;
                    break;
                }
            }
        }

        // Génération ou récupération du code de confirmation côté session (GET)
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            // Générer un code aléatoire de 6 caractères alphanumériques
            $deleteCode = strtoupper(bin2hex(random_bytes(3)));
            $_SESSION['delete_code'] = $deleteCode;
        } else {
            // Si on est en POST, on récupère le code de la session
            $deleteCode = $_SESSION['delete_code'] ?? null;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Si l'utilisateur a des réservations en cours, on bloque la suppression
            if ($hasActiveBookings) {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => "Vous ne pouvez pas supprimer votre compte pour le moment car vous avez des réservations en cours. Contactez le support pour annuler les réservations en cours."
                ];
                // On ajoute le paramètre pour rouvrir la modal automatiquement
                header('Location: /dashboard/profile/delete?modal=delete-account-modal');
                exit;
            }

            // Vérification du code de confirmation
            $confirmCode = $_POST['confirm_code'] ?? '';
            if (!$deleteCode || !$confirmCode || strtoupper($confirmCode) !== $deleteCode) {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => "Le code de confirmation est incorrect."
                ];
                header('Location: /dashboard/profile/delete?modal=delete-account-modal');
                exit;
            }

            // Vérification du mot de passe
            $password = $_POST['password'] ?? null;
            if (!$password) {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => "Le mot de passe est obligatoire."
                ];
                header('Location: /dashboard/profile/delete?modal=delete-account-modal');
                exit;
            }

            if ($user && password_verify($password, $user->getPassword())) {
                // Suppression du compte utilisateur
                try {
                    $this->userModel->deleteUser($user->getId());
                    // On détruit la session proprement pour éviter de perdre l'alerte
                    session_unset();
                    session_destroy();
                    // On redémarre une session pour stocker l'alerte
                    session_start();
                    $_SESSION['alert'] = [
                        'status' => 'success',
                        'message' => "Votre compte a bien été supprimé."
                    ];
                    // Redirection vers login, l'alerte sera visible
                    header('Location: /login');
                    exit;
                } catch (\Exception $e) {
                    $_SESSION['alert'] = [
                        'status' => 'error',
                        'message' => "Une erreur est survenue lors de la suppression du compte."
                    ];
                    header('Location: /dashboard/profile/delete?modal=delete-account-modal');
                    exit;
                }
            } else {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => "Mot de passe incorrect."
                ];
                header('Location: /dashboard/profile/delete?modal=delete-account-modal');
                exit;
            }
        }

        echo $this->twig->render('profileController/delete.html.twig', [
            'errors' => [],
            'hasActiveBookings' => $hasActiveBookings,
            'delete_code' => $deleteCode ?? null
        ]);
    }

    public function notifications()
    {
        $errors = [];

        // On suppose que l'utilisateur est connecté et que son id est stocké en session
        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }

        $user = $this->userModel->getUserById($_SESSION['user']['id']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // On récupère la préférence de consentement marketing
            $marketingConsent = isset($_POST['marketingConsent']) ? true : false;

            try {
                $user->setCommercialEmailConsent($marketingConsent);
                $user->setUpdatedAt(new \DateTime());
                $this->userModel->updateUser($user);

                $_SESSION['alert'] = [
                    'status' => 'success',
                    'message' => 'Vos préférences de notification ont été mises à jour avec succès.'
                ];

                // Redirection pour afficher l'alerte de succès
                header('Location: /dashboard/profile/notifications');
                exit;
            } catch (\Exception $e) {
                $errors['general'] = "Une erreur est survenue lors de la mise à jour de vos préférences.";
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => $errors['general'],
                    'errors' => $errors
                ];
            }
        }

        echo $this->twig->render('profileController/notifications.html.twig', [
            'user' => $user,
            'errors' => $errors
        ]);
    }

    public function billingaddress()
    {
        // Vérifier que l'utilisateur est connecté
        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }

        $userId = $_SESSION['user']['id'];
        $user = $this->userModel->getUserById($userId);

        $errors = [];
        $old = [];

        // Utilisation de la nouvelle méthode du modèle pour récupérer toutes les adresses de facturation
        $billingAddresses = $this->billingAddressModel->getAllBillingAddress($userId);

        // On prépare une variable pour l'adresse de facturation en cours d'édition/création (pour le formulaire)
        $billingAddress = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Récupérer et filtrer les données du formulaire
            $name = trim($_POST['address_recipient'] ?? '');
            $country = trim($_POST['country'] ?? '');
            $address = trim($_POST['address_line_1'] ?? '');
            $address2 = trim($_POST['address_line_2'] ?? '');
            $city = trim($_POST['city'] ?? '');
            $state = trim($_POST['state'] ?? '');
            $postalCode = trim($_POST['postal_code'] ?? '');

            $old = [
                'address_recipient' => $name,
                'country' => $country,
                'address_line_1' => $address,
                'address_line_2' => $address2,
                'city' => $city,
                'state' => $state,
                'postal_code' => $postalCode
            ];

            // Validation
            if (empty($name)) {
                $errors['address_recipient'] = "Le nom complet est requis.";
            } elseif (mb_strlen($name) > 100) {
                $errors['address_recipient'] = "Le nom complet ne doit pas dépasser 100 caractères.";
            }

            if (empty($country)) {
                $errors['country'] = "Le pays est requis.";
            } elseif (mb_strlen($country) > 100) {
                $errors['country'] = "Le pays ne doit pas dépasser 100 caractères.";
            }

            if (empty($address)) {
                $errors['address_line_1'] = "La ligne d'adresse 1 est requise.";
            } elseif (mb_strlen($address) > 255) {
                $errors['address_line_1'] = "La ligne d'adresse 1 ne doit pas dépasser 255 caractères.";
            }

            if (!empty($address2) && mb_strlen($address2) > 255) {
                $errors['address_line_2'] = "La ligne d'adresse 2 ne doit pas dépasser 255 caractères.";
            }

            if (empty($city)) {
                $errors['city'] = "La ville est requise.";
            } elseif (mb_strlen($city) > 100) {
                $errors['city'] = "La ville ne doit pas dépasser 100 caractères.";
            }

            if (!empty($state) && mb_strlen($state) > 28) {
                $errors['state'] = "La province/région ne doit pas dépasser 28 caractères.";
            }

            if (empty($postalCode)) {
                $errors['postal_code'] = "Le code postal est requis.";
            } elseif (!preg_match('/^[0-9A-Za-z\- ]{3,12}$/', $postalCode)) {
                $errors['postal_code'] = "Le code postal n'est pas valide.";
            }

            if (empty($errors)) {
                // Création de l'entité BillingAddress pour le formulaire (sera utilisé pour l'affichage)
                $billingAddress = new BillingAddress(
                    null, // id (sera généré)
                    $user, // utilisateur
                    $name,
                    $country,
                    $address,
                    $address2 !== '' ? $address2 : null,
                    $city,
                    $state !== '' ? $state : null,
                    $postalCode,
                    new \DateTime()
                );

                try {
                    $this->billingAddressModel->createBillingAddress($billingAddress);

                    $_SESSION['alert'] = [
                        'status' => 'success',
                        'message' => 'Votre adresse de facturation a été enregistrée avec succès.'
                    ];

                    header('Location: /dashboard/profile/billingaddress');
                    exit;
                } catch (\Exception $e) {
                    $errors['general'] = "Une erreur est survenue lors de l'enregistrement de l'adresse.";
                    // On ne redirige pas, on laisse afficher les erreurs
                }
            } else {
                // En cas d'erreur, on prépare l'entité pour le formulaire
                $billingAddress = new BillingAddress(
                    null,
                    $user,
                    $name,
                    $country,
                    $address,
                    $address2 !== '' ? $address2 : null,
                    $city,
                    $state !== '' ? $state : null,
                    $postalCode,
                    new \DateTime()
                );
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'Veuillez corriger les erreurs dans le formulaire.',
                    'errors' => $errors
                ];
            }
        }
        // Afficher le formulaire (GET ou POST avec erreurs)
        echo $this->twig->render('profileController/billingaddress.html.twig', [
            'user' => $user,
            'old' => $old,
            'billing_addresses' => $billingAddresses
        ]);
    }

    //delete billing
}
