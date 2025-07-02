<?php
// src/Routing/Router.php

declare(strict_types=1);

namespace App\Routing;

# Contrôleurs
use App\Controller\DefaultController;
use App\Controller\AuthController;
use App\Controller\ErrorController;
use App\Controller\RoomController;
use App\Controller\BookingController;
use App\Controller\ProfileController;
use App\Controller\RoomTypeController;
use App\Controller\EquipmentController;
use App\Controller\OptionController;
use App\Controller\InvoiceController;
use App\Controller\UserController;
use App\Controller\ImageController;
use App\Controller\DashboardController;
use App\Controller\CouponController;
use App\Controller\EmployeeController;
# Services
use App\Service\DependencyContainer;

class Router
{
    private DependencyContainer $dependencyContainer;
    /** @var array<string, array{0: string, 1: string, 2: array<string>}> */
    private array $pageMappings;
    private string $defaultPage;
    private string $errorPage;
    private ?string $currentRoute = null;

    public function __construct(DependencyContainer $dependencyContainer)
    {
        $this->dependencyContainer = $dependencyContainer;

        $mappings = [
            // Page d'accueil
            '' => [DefaultController::class, 'home', []],
            'about' => [DefaultController::class, 'about', []],
            'legals' => [DefaultController::class, 'legals', []],

            // Pages d'erreur
            '403' => [ErrorController::class, 'error403', []],
            '404' => [ErrorController::class, 'error404', []],
            '500' => [ErrorController::class, 'error500', []],

            // Pages d'authentification
            'login' => [AuthController::class, 'login', []],
            'register' => [AuthController::class, 'register', []],
            'logout' => [AuthController::class, 'logout', ['Admin', 'User']],
            'resetpassword' => [AuthController::class, 'resetPassword', []],

            // Recherche de chambres
            'search' => [RoomController::class, 'searchRooms', []],
            'rooms/{room_id}' => [RoomController::class, 'room', []],

            // Page de réservation
            'book' => [BookingController::class, 'bookRoom', []],

            'dashboard' => [DashboardController::class, 'dashboard', ['Admin', 'User']],

            // Profil utilisateur
            'dashboard/profile' => [ProfileController::class, 'profile', ['Admin', 'User']],
            'dashboard/profile/edit' => [ProfileController::class, 'editPersonalInformation', ['Admin', 'User']],
            'dashboard/profile/password' => [ProfileController::class, 'password', ['Admin', 'User']],
            'dashboard/profile/password/edit' => [ProfileController::class, 'editPassword', ['Admin', 'User']],
            'dashboard/profile/billingaddress' => [ProfileController::class, 'billingAddress', ['Admin', 'User']],
            'dashboard/profile/billingaddress/edit' => [ProfileController::class, 'editBillingAddress', ['Admin', 'User']],
            'dashboard/profile/delete' => [ProfileController::class, 'deleteAccount', ['Admin', 'User']],
            'dashboard/profile/notifications' => [ProfileController::class, 'notifications', ['Admin', 'User']],

            // Chambres
            'dashboard/rooms' => [RoomController::class, 'rooms', ['Admin']],
            'dashboard/rooms/add' => [RoomController::class, 'addRoom', ['Admin']],
            'dashboard/rooms/edit/{room_id}' => [RoomController::class, 'editRoom', ['Admin']],
            'dashboard/rooms/delete/{room_id}' => [RoomController::class, 'deleteRoom', ['Admin']],

            // Images
            'dashboard/images' => [ImageController::class, 'images', ['Admin']],
            'dashboard/images/add' => [ImageController::class, 'addImage', ['Admin']],

            'dashboard/images/download/{image_id}' => [ImageController::class, 'downloadImage', ['Admin']],
            'dashboard/images/edit/{image_id}' => [ImageController::class, 'editImage', ['Admin']],
            'dashboard/images/delete/{image_id}' => [ImageController::class, 'deleteImage', ['Admin']],

            // Types de chambres
            'dashboard/roomtypes' => [RoomTypeController::class, 'roomTypes', ['Admin']],
            'dashboard/roomtypes/add' => [RoomTypeController::class, 'addRoomType', ['Admin']],
            'dashboard/roomtypes/edit/{roomtype_id}' => [RoomTypeController::class, 'editRoomType', ['Admin']],
            'dashboard/roomtypes/delete/{roomtype_id}' => [RoomTypeController::class, 'deleteRoomType', ['Admin']],

            // Equipements
            'dashboard/equipments' => [EquipmentController::class, 'equipments', ['Admin']],
            'dashboard/equipments/add' => [EquipmentController::class, 'addEquipment', ['Admin']],
            'dashboard/equipments/edit/{equipment_id}' => [EquipmentController::class, 'editEquipment', ['Admin']],
            'dashboard/equipments/delete/{equipment_id}' => [EquipmentController::class, 'deleteEquipment', ['Admin']],

            // Options
            'dashboard/options' => [OptionController::class, 'options', ['Admin']],
            'dashboard/options/add' => [OptionController::class, 'addOption', ['Admin']],
            'dashboard/options/edit/{option_id}' => [OptionController::class, 'editOption', ['Admin']],
            'dashboard/options/delete/{option_id}' => [OptionController::class, 'deleteOption', ['Admin']],

            // Réservations
            'dashboard/bookings' => [BookingController::class, 'bookings', ['Admin', 'User']],
            'dashboard/bookings/add' => [BookingController::class, 'addBooking', ['Admin']],
            'dashboard/bookings/edit/{booking_id}' => [BookingController::class, 'editBooking', ['Admin']],
            'dashboard/bookings/delete/{booking_id}' => [BookingController::class, 'deleteBooking', ['Admin']],

            // Factures
            'dashboard/invoices/{invoice_id}' => [InvoiceController::class, 'invoice', ['Admin', 'User']],
            'dashboard/invoices' => [InvoiceController::class, 'invoices', ['Admin', 'User']],
            'dashboard/invoices/add' => [InvoiceController::class, 'addInvoice', ['Admin']],
            'dashboard/invoices/edit/{invoice_id}' => [InvoiceController::class, 'editInvoice', ['Admin']],
            'dashboard/invoices/delete/{invoice_id}' => [InvoiceController::class, 'deleteInvoice', ['Admin']],

            // Coupons
            'dashboard/coupons' => [CouponController::class, 'coupons', ['Admin']],
            'dashboard/coupons/add' => [CouponController::class, 'addCoupon', ['Admin']],
            'dashboard/coupons/edit/{coupon_id}' => [CouponController::class, 'editCoupon', ['Admin']],
            'dashboard/coupons/delete/{coupon_id}' => [couponController::class, 'deleteCoupon', ['Admin']],

            // Utilisateurs
            'dashboard/users' => [UserController::class, 'users', ['Admin']],
            'dashboard/users/add' => [UserController::class, 'addUser', ['Admin']],
            'dashboard/users/edit/{user_id}' => [UserController::class, 'editUser', ['Admin']],
            'dashboard/users/delete/{user_id}' => [UserController::class, 'deleteUser', ['Admin']],

            // Employés
            'dashboard/employees' => [EmployeeController::class, 'employees', ['Admin']],
            'dashboard/employees/add' => [EmployeeController::class, 'addEmployee', ['Admin']],
            'dashboard/employees/edit/{employee_id}' => [EmployeeController::class, 'editEmployee', ['Admin']],
            'dashboard/employees/delete/{employee_id}' => [EmployeeController::class, 'deleteEmployee', ['Admin']],
        ];

        /**
         * @var array<string, array{0: class-string, 1: string, 2: array<string>}> $mappings
         */
        $this->pageMappings = $mappings;

        $this->defaultPage = '';
        $this->errorPage = '404';
    }
    /**
     * Regarde si l'utilisateur a la permission d'acceder a la page
     *
     * @param array<string> $allowedRoles Les roles autorises a acceder a la page
     * @return bool True si l'utilisateur a la permission, sinon False
     */
    private function checkPermission(array $allowedRoles): bool
    {
        // Si aucun role n'est specifie, tout le monde a la permission
        if (empty($allowedRoles)) {
            return true;
        }

        // Si l'utilisateur n'est pas connecte, il n'a pas la permission
        if (!isset($_SESSION['user']) || !isset($_SESSION['user']['role'])) {
            return false;
        }

        $userRole = $_SESSION['user']['role'];

        // Verifiez si le role de l'utilisateur est dans le tableau des roles autorises
        return in_array($userRole, $allowedRoles, true);
    }

    /**
     * Route qui demande le controleur approprie
     *
     * @param mixed $twig Instance de la classe Twig
     * @return void
     */
    public function route($twig): void
    {
        $requestedPage = filter_input(INPUT_GET, 'page', FILTER_SANITIZE_SPECIAL_CHARS);

        if ($requestedPage === null || $requestedPage === false) {
            $requestedPage = $this->defaultPage;
        }

        $this->currentRoute = $requestedPage;
        $params = [];

        foreach ($this->pageMappings as $routePattern => $controllerInfo) {
            // Assurez-vous que $routePattern est une chaîne
            $routePatternStr = (string)$routePattern;

            $regexPattern = preg_replace('/\{[^\/]+\}/', '([^\/]+)', $routePatternStr);
            $regexPattern = "#^" . $regexPattern . "$#";

            if (preg_match($regexPattern, $requestedPage, $matches)) {
                array_shift($matches);
                $params = $matches;
                [$controllerClass, $method, $allowedRoles] = $controllerInfo;

                // On verifie si l'utilisateur a la permission d'acceder a la page
                if (!$this->checkPermission($allowedRoles)) {

                    // Si l'utilisateur n'est pas connecte, redirigez-le vers la page de connexion
                    if (!isset($_SESSION['user'])) {
                        header('Location: /login');
                        exit;
                    }

                    $forbiddenInfo = $this->pageMappings['403'];
                    [$errorControllerClass, $errorMethod] = $forbiddenInfo;
                    $errorController = new $errorControllerClass($twig, $this->dependencyContainer);
                    /** @var callable $callback */
                    $callback = [$errorController, $errorMethod];
                    call_user_func($callback);
                    return;
                }

                if (class_exists($controllerClass) && method_exists($controllerClass, $method)) {
                    $controller = new $controllerClass($twig, $this->dependencyContainer);
                    /** @var callable $callback */
                    $callback = [$controller, $method];
                    call_user_func_array($callback, $params);
                    return;
                }
            }
        }

        $error404Info = $this->pageMappings[$this->errorPage];
        [$errorControllerClass, $errorMethod] = $error404Info;
        $errorController = new $errorControllerClass($twig, $this->dependencyContainer);
        /** @var callable $callback */
        $callback = [$errorController, $errorMethod];
        call_user_func($callback);
    }

    public function getCurrentRoute(): ?string
    {
        return $this->currentRoute;
    }
}
