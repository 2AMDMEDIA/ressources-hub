<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Database;
use App\Models\User;
use PDO;
use Ramsey\Uuid\Uuid;

/**
 * Table de liaison club ⇄ manager (1 club = 1 manager).
 */
final class ClubManagerRepository
{
    private function pdo(): PDO
    {
        return Database::pdo();
    }

    /** Désigne (ou remplace) le manager d'un club. */
    public function setManager(string $clubId, string $userId): void
    {
        $this->pdo()->prepare('DELETE FROM club_managers WHERE club_id = :club')->execute([':club' => $clubId]);
        $stmt = $this->pdo()->prepare(
            'INSERT INTO club_managers (id, club_id, user_id) VALUES (:id, :club, :user)'
        );
        $stmt->execute([':id' => Uuid::uuid4()->toString(), ':club' => $clubId, ':user' => $userId]);
    }

    /** Le manager d'un club, ou null. */
    public function managerOf(string $clubId): ?User
    {
        $stmt = $this->pdo()->prepare(
            'SELECT u.* FROM club_managers cm JOIN users u ON u.id = cm.user_id WHERE cm.club_id = :club LIMIT 1'
        );
        $stmt->execute([':club' => $clubId]);
        $row = $stmt->fetch();
        return $row ? User::fromRow($row) : null;
    }

    public function clubIdOf(string $userId): ?string
    {
        $stmt = $this->pdo()->prepare('SELECT club_id FROM club_managers WHERE user_id = :user LIMIT 1');
        $stmt->execute([':user' => $userId]);
        $val = $stmt->fetchColumn();
        return $val === false ? null : (string) $val;
    }

    public function isManager(string $userId): bool
    {
        return $this->clubIdOf($userId) !== null;
    }
}
