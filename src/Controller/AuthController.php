<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Entity\Coupon;
use App\Service\DependencyContainer;
use Twig\Environment;
use App\Service\Mailer;

class AuthController
{
    private $twig;
    private $userModel;
    private $couponModel;
    private $mailer;

    public function __construct(Environment $twig, DependencyContainer $dependencyContainer)
    {
        $this->twig = $twig;
        $this->userModel = $dependencyContainer->get('UserModel');
        $this->couponModel = $dependencyContainer->get('CouponModel');
        $this->mailer = new Mailer($twig);
    }

    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
            $password = $_POST['password'];

            if (!$email) {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'Email ou mot de passe incorrect.',
                ];
                header('Location: /login');
                exit;
            }
            if (!$password) {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'Email ou mot de passe incorrect.',
                ];
                header('Location: /login');
                exit;
            }

            $user = $this->userModel->getUserByEmail($email);

            if ($_ENV['APP_MAINTENANCE'] === 'true') {
                if ($user && $user->getRole() !== 'Admin') {
                    $_SESSION['alert'] = [
                        'status' => 'error',
                        'message' => 'Le site est en maintenance. Veuillez réessayer plus tard.',
                    ];
                    header('Location: /login');
                    exit;
                }
            }

            if ($user && password_verify($password, $user->getPassword())) {
                $_SESSION['user'] = [
                    'id' => $user->getId(),
                    'name' => $user->getName(),
                    'email' => $user->getEmail(),
                    'type' => $user->getType(),
                    'role' => $user->getRole(),
                ];
                header('Location: /dashboard');
                exit;
            } else {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'Email ou mot de passe incorrect.',
                ];
                header('Location: /login');
                exit;
            }
        }

        echo $this->twig->render('authController/login.html.twig', []);
    }
    public function resetpassword()
    {

        if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['token']) && isset($_GET['reset_user'])) {
            $token = $_GET['token'];
            $user = $this->userModel->getUserById($_GET['reset_user']);

            if ($user && $user->getResetTokenExpiresAt() > new \DateTime() && $token === $user->getResetToken()) {
                echo $this->twig->render('authController/resetpassword.html.twig', ['reset_user' => $user->getId(), 'token' => $token]);
                exit;
            } else {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'Le lien de réinitialisation de mot de passe est invalide ou a expiré.',
                ];
                header('Location: /resetpassword');
                exit;
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['token']) && isset($_POST['reset_user']) && isset($_POST['password']) && isset($_POST['confirm-password'])) {

            $token = $_GET['token'];
            $user = $this->userModel->getUserById($_GET['reset_user']);

            if (!($user && $user->getResetTokenExpiresAt() > new \DateTime() && $token === $user->getResetToken())) {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'Le lien de réinitialisation de mot de passe est invalide ou a expiré.',
                ];
                header('Location: /resetpassword');
                exit;
            }

            if (!$user) {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'Une erreur s\'est produite. Veuillez réessayer.',
                ];
                header('Location: /resetpassword' . '?reset_user=' . $_POST['reset_user'] . '&token=' . $_POST['token']);
                exit;
            }

            $password = $_POST['password'];
            $confirmPassword = $_POST['confirm-password'];

            if (!$password || !$confirmPassword) {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'Les mots de passe sont obligatoires.',
                ];
                header('Location: /resetpassword' . '?reset_user=' . $_POST['reset_user'] . '&token=' . $_POST['token']);
                exit;
            }

            if ($password !== $confirmPassword) {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'Les mots de passe ne correspondent pas.',
                ];
                header('Location: /resetpassword' . '?reset_user=' . $_POST['reset_user'] . '&token=' . $_POST['token']);
                exit;
            }

            $passwordLength = strlen($password);
            $containsDigit = preg_match('/\d/', $password);
            $containsUpper = preg_match('/[A-Z]/', $password);
            $containsLower = preg_match('/[a-z]/', $password);
            $containsSpecial = preg_match('/[^a-zA-Z\d]/', $password);

            if ($passwordLength < 8 || !$containsDigit || !$containsUpper || !$containsLower || !$containsSpecial) {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'Le mot de passe doit contenir au moins 8 caractères, une lettre majuscule, une lettre minuscule, un chiffre et un caractère spécial.',
                ];
                header('Location: /resetpassword' . '?reset_user=' . $_POST['reset_user'] . '&token=' . $_POST['token']);
                exit;
            }

            $user->setPassword($password);
            $user->setResetToken(null);
            $user->setResetTokenExpiresAt(null);
            $this->userModel->updateUser($user);

            $_SESSION['alert'] = [
                'status' => 'success',
                'scope' => 'reset-password',
            ];
            header('Location: /resetpassword');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);

            if (!$email) {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'L\'email est obligatoire.',
                ];
                header('Location: /resetpassword');
                exit;
            }

            $user = $this->userModel->getUserByEmail($email);

            if ($user) {
                $resetToken = bin2hex(random_bytes(127));
                $user->setResetToken($resetToken);
                $user->setResetTokenExpiresAt(new \DateTime('+1 hour'));
                $this->userModel->updateUser($user);

                $this->mailer->sendResetPassword($user->getName(), $user->getEmail(), $resetToken, $user->getId());
            }

            $_SESSION['alert'] = [
                'status' => 'success',
                'scope' => 'send-reset-password',
            ];

            header('Location: /resetpassword');
            exit;
        }

        echo $this->twig->render('authController/resetpassword.html.twig', []);
    }

    public function register()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            if (isset($_POST['user_uuid'])) {
                $user = $this->userModel->getUserById($_POST['user_uuid']);
                if (!$user) {
                    $_SESSION['alert'] = [
                        'status' => 'error',
                        'message' => 'Une erreur s\'est produite. Veuillez réessayer.',
                    ];
                    header('Location: /register');
                    exit;
                }
                $code = $_POST['code_1'] . $_POST['code_2'] . $_POST['code_3'] . $_POST['code_4'] . $_POST['code_5'] . $_POST['code_6'];
                if ($code === $user->getActivationToken()) {
                    $user->setIsActive(true);
                    $user->setActivationToken(null);
                    $this->userModel->updateUser($user);

                    $coupon = new Coupon(
                        null,
                        'MERCI' . substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 13),
                        5,
                        null,
                        1,
                        $user,
                        new \DateTime('+6 months'),
                        new \DateTime(),
                        new \DateTime()
                    );

                    $this->couponModel->createCoupon($coupon, $user->getId());
                    $this->mailer->sendAccountCreation($user->getName(), $user->getEmail(), $coupon->getCode());

                    $_SESSION['alert'] = [
                        'status' => 'success',
                        'scope' => 'create-account',
                    ];
                    header('Location: /register');
                    exit;
                } else {
                    $_SESSION['alert'] = [
                        'status' => 'error',
                        'message' => 'Le code de confirmation est invalide. Veuillez réessayer.',
                    ];
                    echo $this->twig->render('authController/confirm.html.twig', []);
                    exit;
                }
            } else {



                if (!isset($_POST['name']) || !isset($_POST['email']) || !isset($_POST['password']) || !isset($_POST['confirm-password'])) {
                    $_SESSION['alert'] = [
                        'status' => 'error',
                        'saved_content' => [
                            'name' => $_POST['name'],
                            'email' => $_POST['email'],
                        ],
                        'message' => 'Tous les champs sont obligatoires.',
                    ];
                    header('Location: /register');
                    exit;
                }

                $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS);
                $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
                $type = $_POST['type'];
                $password = $_POST['password'];
                $confirmPassword = $_POST['confirm-password'];

                $errors = [];

                if ($type !== 'individual' && $type !== 'company') {
                    $errors['type'] = "Le type de compte est obligatoire.";
                }

                if (!$email) {
                    $errors['email'] = "Email invalide.";
                }

                if ($password !== $confirmPassword) {
                    $errors['confirmpassword'] = " Les mots de passe ne correspondent pas.";
                }

                if ($type === 'individual') {
                    $containsValidName = preg_match('/^[A-Za-z]+ [A-Za-z]+(?: [A-Za-z]+)*$/', $name);
                    if (!$containsValidName) {
                        $errors['name'] = "Nom invalide";
                    }
                } else {
                    $containsValidName = preg_match("/^[A-Za-zÀ-ÖØ-öø-ÿ0-9&.,' -]{2,50}$/u", $name);
                    if (!$containsValidName) {
                        $errors['name'] = "Nom invalide";
                    }
                }

                $passwordLength = strlen($password);
                $containsDigit = preg_match('/\d/', $password);
                $containsUpper = preg_match('/[A-Z]/', $password);
                $containsLower = preg_match('/[a-z]/', $password);
                $containsSpecial = preg_match('/[^a-zA-Z\d]/', $password);

                if ($passwordLength < 8 || !$containsDigit || !$containsUpper || !$containsLower || !$containsSpecial) {
                    $errors['password'] = "Le mot de passe doit contenir au moins 8 caractères, une lettre majuscule, une lettre minuscule, un chiffre et un caractère spécial. ";
                }

                if (!$type) {
                    $errors['type'] = "Le type de compte est obligatoire.";
                }

                if (!$name) {
                    $errors['name'] = "Le nom est obligatoire.";
                }
                if (!$_POST['email']) {
                    $errors['email'] = "L'email est obligatoire.";
                }
                if (!$password) {
                    $errors['password'] = "Le mot de passe est obligatoire.";
                }
                if (!$confirmPassword) {
                    $errors['confirmpassword'] = "La confirmation du mot de passe est obligatoire.";
                }


                if (empty($errors)) {

                    if ($email) {
                        $user = $this->userModel->getUserByEmail($email);

                        if ($user && $user->getIsActive()) {
                            $_SESSION['alert'] = [
                                'status' => 'error',
                                'saved_content' => [
                                    'type' => $type,
                                    'name' => $name,
                                    'email' => $email,
                                ],
                                'message' => 'Un compte utilisateur actif existe déjà avec cet email. Essayer d\'utiliser un autre email ou de vous connecter avec cet email.',
                            ];
                            header('Location: /register');
                            exit;
                        }
                    }

                    $user = new User(
                        null,
                        $name,
                        $email,
                        null,
                        false,
                        $type,
                        password_hash($password, PASSWORD_DEFAULT),
                        false,
                        substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 6),
                        new \DateTime('+1 day'),
                        null,
                        null,
                        'USER',
                        new \DateTime(),
                        new \DateTime()
                    );

                    $result = $this->userModel->upsertUser($user);
                    if ($result) {
                        $this->mailer->sendConfirmEmail($user->getName(), $user->getEmail(), $user->getActivationToken());
                    }

                    $user = $this->userModel->getUserByEmail($email);
                    echo $this->twig->render('authController/confirm.html.twig', ['user_uuid' => $user->getId()]);
                    exit;
                } else {
                    $_SESSION['alert'] = [
                        'status' => 'error',
                        'errors' => $errors,
                        'saved_content' => [
                            'type' => $type,
                            'name' => $name,
                            'email' => $email,
                        ],
                        'message' => 'Certaines informations sont incorrectes. Veuillez vérifier les champs invalides. ',
                    ];
                }
            }
            header('Location: /register');
            exit;
        }
        echo $this->twig->render('authController/register.html.twig', []);
    }

    public function logout()
    {

        session_destroy();
        header('Location: /login');
        exit;
    }
}
