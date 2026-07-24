<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Database;
use App\Models\Employee;
use PDO;
use Ramsey\Uuid\Uuid;

final class EmployeeRepository
{
    private function pdo(): PDO
    {
        return Database::pdo();
    }

    public function findById(string $id): ?Employee
    {
        $stmt = $this->pdo()->prepare('SELECT * FROM employees WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ? Employee::fromRow($row) : null;
    }

    /** @return list<Employee> */
    public function listByClub(string $clubId): array
    {
        $stmt = $this->pdo()->prepare(
            'SELECT * FROM employees WHERE club_id = :club ORDER BY last_name ASC, first_name ASC'
        );
        $stmt->execute([':club' => $clubId]);
        return array_map([Employee::class, 'fromRow'], $stmt->fetchAll());
    }

    /**
     * Tous les employés avec le nom + id de leur club (page globale).
     * @return list<array<string,mixed>>
     */
    public function listAllWithClub(): array
    {
        $sql = 'SELECT e.*, c.name AS club_name, c.city AS club_city
                FROM employees e
                JOIN clubs c ON c.id = e.club_id
                ORDER BY c.name ASC, e.last_name ASC, e.first_name ASC';
        return $this->pdo()->query($sql)->fetchAll();
    }

    public function create(string $clubId, string $firstName, string $lastName, ?string $email, ?string $jobTitle): string
    {
        $id = Uuid::uuid4()->toString();
        $stmt = $this->pdo()->prepare(
            'INSERT INTO employees (id, club_id, first_name, last_name, email, job_title)
             VALUES (:id, :club, :first, :last, :email, :job)'
        );
        $stmt->execute([
            ':id' => $id,
            ':club' => $clubId,
            ':first' => $firstName,
            ':last' => $lastName,
            ':email' => $email,
            ':job' => $jobTitle,
        ]);
        return $id;
    }

    public function update(string $id, string $firstName, string $lastName, ?string $email, ?string $jobTitle): void
    {
        $stmt = $this->pdo()->prepare(
            'UPDATE employees SET first_name = :first, last_name = :last, email = :email,
                    job_title = :job, updated_at = NOW() WHERE id = :id'
        );
        $stmt->execute([
            ':first' => $firstName,
            ':last' => $lastName,
            ':email' => $email,
            ':job' => $jobTitle,
            ':id' => $id,
        ]);
    }

    public function setUserId(string $id, ?string $userId): void
    {
        $stmt = $this->pdo()->prepare('UPDATE employees SET user_id = :uid, updated_at = NOW() WHERE id = :id');
        $stmt->execute([':uid' => $userId, ':id' => $id]);
    }

    public function delete(string $id): void
    {
        $this->pdo()->prepare('DELETE FROM employees WHERE id = :id')->execute([':id' => $id]);
    }
}
