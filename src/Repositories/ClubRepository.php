<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Database;
use App\Models\Club;
use PDO;
use Ramsey\Uuid\Uuid;

final class ClubRepository
{
    private function pdo(): PDO
    {
        return Database::pdo();
    }

    public function findById(string $id): ?Club
    {
        $stmt = $this->pdo()->prepare('SELECT * FROM clubs WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ? Club::fromRow($row) : null;
    }

    /** @return list<Club> */
    public function all(): array
    {
        $rows = $this->pdo()->query('SELECT * FROM clubs ORDER BY name ASC')->fetchAll();
        return array_map([Club::class, 'fromRow'], $rows);
    }

    public function create(string $name, ?string $contactEmail = null, int $seatsLimit = 1, ?string $contractRef = null): Club
    {
        $id = Uuid::uuid4()->toString();
        $stmt = $this->pdo()->prepare(
            'INSERT INTO clubs (id, name, status, seats_limit, contact_email, contract_ref)
             VALUES (:id, :name, :status, :seats, :email, :ref)'
        );
        $stmt->execute([
            ':id' => $id,
            ':name' => $name,
            ':status' => 'active',
            ':seats' => $seatsLimit,
            ':email' => $contactEmail,
            ':ref' => $contractRef,
        ]);
        $club = $this->findById($id);
        if ($club === null) {
            throw new \RuntimeException('Création club échouée.');
        }
        return $club;
    }

    public function setOwner(string $clubId, string $userId): void
    {
        $stmt = $this->pdo()->prepare('UPDATE clubs SET owner_user_id = :uid, updated_at = NOW() WHERE id = :id');
        $stmt->execute([':uid' => $userId, ':id' => $clubId]);
    }

    /** @param string $status 'active' | 'suspended' | 'closed' */
    public function setStatus(string $clubId, string $status): void
    {
        $stmt = $this->pdo()->prepare('UPDATE clubs SET status = :status, updated_at = NOW() WHERE id = :id');
        $stmt->execute([':status' => $status, ':id' => $clubId]);
    }
}
