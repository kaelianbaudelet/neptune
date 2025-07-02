<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\DependencyContainer;
use Twig\Environment;

use App\Entity\Employee;

class EmployeeController
{
    private $twig;
    private $employeeModel;

    public function __construct(Environment $twig, DependencyContainer $dependencyContainer)
    {
        $this->twig = $twig;
        $this->employeeModel = $dependencyContainer->get('EmployeeModel');
    }

    public function employees()
    {
        $employees = $this->employeeModel->getAllEmployees();
        echo $this->twig->render('employeeController/employees.html.twig', [
            'employees' => $employees
        ]);
    }

    public function addEmployee()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = filter_var($_POST['name'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
            $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
            $phone = filter_var($_POST['phone'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
            $position = filter_var($_POST['position'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
            $salary = filter_var($_POST['salary'] ?? 0, FILTER_VALIDATE_FLOAT);
            $dateOfBirth = !empty($_POST['date_of_birth']) ? new \DateTime($_POST['date_of_birth']) : null;
            $hiredAt = !empty($_POST['hired_at']) ? new \DateTime($_POST['hired_at']) : new \DateTime();

            $errors = [];

            if (empty($name)) {
                $errors['name'] = 'Le nom est requis.';
            }

            if (empty($email)) {
                $errors['email'] = 'L\'email est requis.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'L\'email n\'est pas valide.';
            }

            if (empty($phone)) {
                $errors['phone'] = 'Le numéro de téléphone est requis.';
            }

            if (empty($position)) {
                $errors['position'] = 'Le poste est requis.';
            }

            if (empty($salary)) {
                $errors['salary'] = 'Le salaire est requis.';
            }

            if (empty($dateOfBirth)) {
                $errors['date_of_birth'] = 'La date de naissance est requise.';
            }

            if (!empty($errors)) {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'Veuillez corriger les erreurs dans le formulaire.',
                    'context' => 'modal',
                    'errors' => $errors
                ];
                header('Location: /dashboard/employees?modal=create-employee');
                exit;
            }

            $employee = new Employee(
                null,
                $name,
                $email,
                $phone,
                $position,
                (float)$salary,
                $dateOfBirth,
                $hiredAt,
                new \DateTime(),
                new \DateTime()
            );

            $this->employeeModel->createEmployee($employee);
            $_SESSION['alert'] = [
                'status' => 'success',
                'message' => 'L\'employé a été créé avec succès.',
                'context' => 'global'
            ];
            header('Location: /dashboard/employees');
            exit;
        }

        header('Location: /dashboard/employees');
        exit;
    }

    public function deleteEmployee(string $employee_id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $employee = $this->employeeModel->getEmployeeById($employee_id);

            if (!$employee) {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'L\'employé n\'existe pas.',
                    'context' => 'global'
                ];
                header('Location: /dashboard/employees');
                exit;
            }

            $this->employeeModel->deleteEmployee($employee_id);
            $_SESSION['alert'] = [
                'status' => 'success',
                'message' => 'L\'employé a été supprimé avec succès.',
                'context' => 'global'
            ];
            header('Location: /dashboard/employees');
            exit;
        }

        header('Location: /dashboard/employees');
        exit;
    }

    public function editEmployee(string $employee_id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = filter_var($_POST['name'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
            $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
            $phone = filter_var($_POST['phone'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
            $position = filter_var($_POST['position'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
            $salary = filter_var($_POST['salary'] ?? 0, FILTER_VALIDATE_FLOAT);
            $dateOfBirth = !empty($_POST['date_of_birth']) ? new \DateTime($_POST['date_of_birth']) : null;
            $hiredAt = !empty($_POST['hired_at']) ? new \DateTime($_POST['hired_at']) : null;

            $errors = [];

            if (empty($name)) {
                $errors['name'] = 'Le nom est requis.';
            }

            if (empty($email)) {
                $errors['email'] = 'L\'email est requis.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'L\'email n\'est pas valide.';
            }

            if (empty($phone)) {
                $errors['phone'] = 'Le numéro de téléphone est requis.';
            }

            if (empty($position)) {
                $errors['position'] = 'Le poste est requis.';
            }

            if (empty($salary)) {
                $errors['salary'] = 'Le salaire est requis.';
            }

            if (empty($dateOfBirth)) {
                $errors['date_of_birth'] = 'La date de naissance est requise.';
            }

            if (!empty($errors)) {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'Veuillez corriger les erreurs dans le formulaire.',
                    'context' => 'modal',
                    'errors' => $errors
                ];
                header('Location: /dashboard/employees/edit/' . $employee_id);
                exit;
            }

            $employee = $this->employeeModel->getEmployeeById($employee_id);
            if (!$employee) {
                $_SESSION['alert'] = [
                    'status' => 'error',
                    'message' => 'L\'employé n\'existe pas.',
                    'context' => 'global'
                ];
                header('Location: /dashboard/employees');
                exit;
            }

            $employee->setName($name);
            $employee->setEmail($email);
            $employee->setPhone($phone);
            $employee->setPosition($position);
            $employee->setSalary((float)$salary);
            $employee->setDateOfBirth($dateOfBirth);
            $employee->setHiredAt($hiredAt);
            $employee->setUpdatedAt(new \DateTime());

            $this->employeeModel->updateEmployee($employee);

            $_SESSION['alert'] = [
                'status' => 'success',
                'message' => 'L\'employé a été mis à jour avec succès.',
                'context' => 'global'
            ];
            header('Location: /dashboard/employees');
            exit;
        }

        $employee = $this->employeeModel->getEmployeeById($employee_id);
        if (!$employee) {
            $_SESSION['alert'] = [
                'status' => 'error',
                'message' => 'L\'employé n\'existe pas.',
                'context' => 'global'
            ];
            header('Location: /dashboard/employees');
            exit;
        }

        echo $this->twig->render('employeeController/editEmployee.html.twig', [
            'employee' => $employee
        ]);
    }
}
