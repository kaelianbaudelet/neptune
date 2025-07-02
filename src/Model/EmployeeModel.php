<?php

declare(strict_types=1);

namespace App\Model;

use App\Entity\Employee;
use PDO;


class EmployeeModel
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function createEmployee(Employee $employee): bool
    {
        $sql = "INSERT INTO Employee (name, email, phone, position, salary, date_of_birth, hired_at, created_at, updated_at)
                VALUES (:name, :email, :phone, :position, :salary, :date_of_birth, :hired_at, :created_at, :updated_at)";

        $stmt = $this->db->prepare($sql);

        $stmt->bindValue(':name', $employee->getName());
        $stmt->bindValue(':email', $employee->getEmail());
        $stmt->bindValue(':phone', $employee->getPhone());
        $stmt->bindValue(':position', $employee->getPosition());
        $stmt->bindValue(':salary', $employee->getSalary());
        $stmt->bindValue(':date_of_birth', $employee->getDateOfBirth()->format('Y-m-d'));
        $stmt->bindValue(':hired_at', $employee->getHiredAt()->format('Y-m-d H:i:s'));
        $stmt->bindValue(':created_at', $employee->getCreatedAt()->format('Y-m-d H:i:s'));
        $stmt->bindValue(':updated_at', $employee->getUpdatedAt()->format('Y-m-d H:i:s'));

        return $stmt->execute();
    }

    public function getEmployeeById(string $id): ?Employee
    {
        $sql = "SELECT * FROM Employee WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return new Employee(
            $row['id'],
            $row['name'],
            $row['email'],
            $row['phone'],
            $row['position'],
            (float)$row['salary'],
            new \DateTime($row['date_of_birth']),
            new \DateTime($row['hired_at']),
            new \DateTime($row['created_at']),
            new \DateTime($row['updated_at'])
        );
    }

    public function updateEmployee(Employee $employee): bool
    {
        $sql = "UPDATE Employee
                SET name = :name,
                    email = :email,
                    phone = :phone,
                    position = :position,
                    salary = :salary,
                    date_of_birth = :date_of_birth,
                    hired_at = :hired_at,
                    updated_at = :updated_at
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);

        $stmt->bindValue(':name', $employee->getName());
        $stmt->bindValue(':email', $employee->getEmail());
        $stmt->bindValue(':phone', $employee->getPhone());
        $stmt->bindValue(':position', $employee->getPosition());
        $stmt->bindValue(':salary', $employee->getSalary());
        $stmt->bindValue(':date_of_birth', $employee->getDateOfBirth()->format('Y-m-d'));
        $stmt->bindValue(':hired_at', $employee->getHiredAt()->format('Y-m-d H:i:s'));
        $stmt->bindValue(':updated_at', $employee->getUpdatedAt()->format('Y-m-d H:i:s'));
        $stmt->bindValue(':id', $employee->getId());

        return $stmt->execute();
    }

    public function getAllEmployees(): array
    {
        $sql = "SELECT * FROM Employee";
        $stmt = $this->db->query($sql);

        $employees = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $employees[] = new Employee(
                $row['id'],
                $row['name'],
                $row['email'],
                $row['phone'],
                $row['position'],
                (float)$row['salary'],
                new \DateTime($row['date_of_birth']),
                new \DateTime($row['hired_at']),
                new \DateTime($row['created_at']),
                new \DateTime($row['updated_at'])
            );
        }

        return $employees;
    }

    public function deleteEmployee(string $id): bool
    {
        $sql = "DELETE FROM Employee WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id);

        return $stmt->execute();
    }
}
