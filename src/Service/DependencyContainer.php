<?php

namespace App\Service;

use App\Model\RoomModel;
use App\Model\CouponModel;
use App\Model\UserModel;
use App\Model\ImageModel;
use App\Model\TypeModel;
use App\Model\InvoiceModel;
use App\Model\OptionModel;
use App\Model\BookingModel;
use App\Model\EquipmentModel;
use App\Model\EmployeeModel;

use App\Service\Mailer;

use Twig\Environment;

use PDO;
use PDOException;

class DependencyContainer
{
    private $instances = [];
    private $twig;

    public function __construct() {}

    public function get($key)
    {
        if (!isset($this->instances[$key])) {
            $this->instances[$key] = $this->createInstance($key);
        }

        return $this->instances[$key];
    }

    private function createInstance($key)
    {
        switch ($key) {
            case 'PDO':
                return $this->createPDOInstance();
            case 'Twig':
                if (!$this->twig) {
                    $loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../../templates');
                    $this->twig = new Environment($loader);
                }
                return $this->twig;
            case 'EmailService':
                return new Mailer($this->get('Twig'));
            case 'RoomModel':
                $pdo = $this->get('PDO');
                return new RoomModel($pdo);
            case 'EmployeeModel':
                $pdo = $this->get('PDO');
                return new EmployeeModel($pdo);
            case 'UserModel':
                $pdo = $this->get('PDO');
                return new UserModel($pdo);
            case 'TypeModel':
                $pdo = $this->get('PDO');
                return new TypeModel($pdo);
            case 'CouponModel':
                $pdo = $this->get('PDO');
                return new CouponModel($pdo);
            case 'ImageModel':
                $pdo = $this->get('PDO');
                return new ImageModel($pdo);
            case 'InvoiceModel':
                $pdo = $this->get('PDO');
                return new InvoiceModel($pdo);
            case 'OptionModel':
                $pdo = $this->get('PDO');
                return new OptionModel($pdo);
            case 'BookingModel':
                $pdo = $this->get('PDO');
                return new BookingModel($pdo);
            case 'EquipmentModel':
                $pdo = $this->get('PDO');
                return new EquipmentModel($pdo);
            case 'BillingAddressModel':
                $pdo = $this->get('PDO');
                return new \App\Model\BillingAddressModel($pdo);
            default:
                throw new \Exception("No service found for key: " . $key);
        }
    }

    private function createPDOInstance()
    {
        try {
            $pdo = new PDO('mysql:host=' . $_ENV['DATABASE_HOST'] . ';port=' . $_ENV['DATABASE_PORT'] . ';dbname=' .
                $_ENV['DATABASE_NAME'] . ';charset=utf8', $_ENV['DATABASE_USER'], $_ENV['DATABASE_PASSWORD']);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        } catch (PDOException $e) {
            throw new \Exception("PDO connection error: " . $e->getMessage());
        }
    }
}
