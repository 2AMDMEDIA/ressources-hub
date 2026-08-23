<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Database;
use PDO;
use Ramsey\Uuid\Uuid;

final class ContactMessageRepository
{
    private function pdo(): PDO
    {
        return Database::pdo();
    }

    /** @return list<array<string,mixed>> */
    public function all(): array
    {
        return $this->pdo()->query('SELECT * FROM contact_messages ORDER BY created_at DESC')->fetchAll();
    }

    /** @return array<string,mixed>|null */
    public function findById(string $id): ?array
    {
        $stmt = $this->pdo()->prepare('SELECT * FROM contact_messages WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function countNew(): int
    {
        return (int) $this->pdo()->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'new'")->fetchColumn();
    }

    /** @param string $status 'new' | 'read' | 'archived' */
    public function setStatus(string $id, string $status): void
    {
        $stmt = $this->pdo()->prepare('UPDATE contact_messages SET status = :s WHERE id = :id');
        $stmt->execute([':s' => $status, ':id' => $id]);
    }

    public function delete(string $id): void
    {
        $this->pdo()->prepare('DELETE FROM contact_messages WHERE id = :id')->execute([':id' => $id]);
    }

    /** @param array<string,mixed> $data */
    public function create(array $data): string
    {
        $id = Uuid::uuid4()->toString();
        $stmt = $this->pdo()->prepare(
            'INSERT INTO contact_messages (id, name, first_name, email, phone, club, club_address, subject, message, ip)
             VALUES (:id, :name, :first_name, :email, :phone, :club, :club_address, :subject, :message, :ip)'
        );
        $stmt->execute([
            ':id' => $id,
            ':name' => $data['name'],
            ':first_name' => $data['first_name'] ?? null,
            ':email' => $data['email'],
            ':phone' => $data['phone'] ?? null,
            ':club' => $data['club'] ?? null,
            ':club_address' => $data['club_address'] ?? null,
            ':subject' => $data['subject'] ?? null,
            ':message' => $data['message'],
            ':ip' => $data['ip'] ?? null,
        ]);
        return $id;
    }
}
