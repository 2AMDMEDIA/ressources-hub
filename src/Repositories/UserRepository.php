<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Bootstrap;
use App\Database;
use App\Models\User;
use PDO;
use Ramsey\Uuid\Uuid;

final class UserRepository
{
    private function pdo(): PDO
    {
        return Database::pdo();
    }

    public function findById(string $id): ?User
    {
        $stmt = $this->pdo()->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ? User::fromRow($row) : null;
    }

    public function findByEmail(string $email): ?User
    {
        $stmt = $this->pdo()->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => mb_strtolower($email)]);
        $row = $stmt->fetch();
        return $row ? User::fromRow($row) : null;
    }

    /** @return list<User> Membres d'un club (owner + collaborateurs), triés par rôle puis nom. */
    public function listByClub(string $clubId): array
    {
        $stmt = $this->pdo()->prepare(
            "SELECT * FROM users WHERE club_id = :club ORDER BY FIELD(role,'club_owner','club_member'), full_name ASC"
        );
        $stmt->execute([':club' => $clubId]);
        return array_map([User::class, 'fromRow'], $stmt->fetchAll());
    }

    public function countActiveByClub(string $clubId): int
    {
        $stmt = $this->pdo()->prepare(
            "SELECT COUNT(*) FROM users WHERE club_id = :club AND status = 'active'"
        );
        $stmt->execute([':club' => $clubId]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Crée un utilisateur.
     *
     * @param string $role 'super_admin' | 'club_owner' | 'club_member'
     */
    public function create(
        string $email,
        ?string $plainPassword,
        string $fullName,
        string $role = 'club_member',
        ?string $clubId = null,
        bool $needsPasswordSetup = false,
    ): User {
        $id = Uuid::uuid4()->toString();
        $cost = (int) (Bootstrap::config('app.security.bcrypt_cost') ?? 12);
        $hash = $plainPassword !== null
            ? password_hash($plainPassword, PASSWORD_BCRYPT, ['cost' => $cost])
            : null;
        $isSuperAdmin = $role === 'super_admin';

        $stmt = $this->pdo()->prepare(
            'INSERT INTO users (id, club_id, email, password_hash, full_name, role, is_super_admin, status, needs_password_setup)
             VALUES (:id, :club_id, :email, :hash, :name, :role, :super_admin, :status, :needs_setup)'
        );
        $stmt->execute([
            ':id' => $id,
            ':club_id' => $clubId,
            ':email' => mb_strtolower($email),
            ':hash' => $hash,
            ':name' => $fullName,
            ':role' => $role,
            ':super_admin' => $isSuperAdmin ? 1 : 0,
            ':status' => 'active',
            ':needs_setup' => $needsPasswordSetup ? 1 : 0,
        ]);

        $user = $this->findById($id);
        if ($user === null) {
            throw new \RuntimeException('Création utilisateur échouée.');
        }
        return $user;
    }

    public function updatePassword(string $userId, string $plainPassword): void
    {
        $cost = (int) (Bootstrap::config('app.security.bcrypt_cost') ?? 12);
        $hash = password_hash($plainPassword, PASSWORD_BCRYPT, ['cost' => $cost]);
        $stmt = $this->pdo()->prepare(
            'UPDATE users SET password_hash = :hash, needs_password_setup = 0, updated_at = NOW() WHERE id = :id'
        );
        $stmt->execute([':hash' => $hash, ':id' => $userId]);
    }

    public function touchLastLogin(string $userId): void
    {
        $stmt = $this->pdo()->prepare('UPDATE users SET last_login_at = NOW() WHERE id = :id');
        $stmt->execute([':id' => $userId]);
    }

    public function updateFullName(string $userId, string $fullName): void
    {
        $stmt = $this->pdo()->prepare('UPDATE users SET full_name = :name, updated_at = NOW() WHERE id = :id');
        $stmt->execute([':name' => $fullName, ':id' => $userId]);
    }

    public function updateStatus(string $userId, string $status): void
    {
        $stmt = $this->pdo()->prepare('UPDATE users SET status = :status, updated_at = NOW() WHERE id = :id');
        $stmt->execute([':status' => $status, ':id' => $userId]);
    }
}
