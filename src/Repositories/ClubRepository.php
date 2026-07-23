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

    /**
     * Liste des clubs enrichie du manager (via la table de liaison club_managers).
     * @return list<array<string,mixed>>
     */
    public function listWithStats(): array
    {
        $sql = 'SELECT c.*, m.email AS manager_email, m.full_name AS manager_name
                FROM clubs c
                LEFT JOIN club_managers cm ON cm.club_id = c.id
                LEFT JOIN users m ON m.id = cm.user_id
                ORDER BY c.name ASC';
        return $this->pdo()->query($sql)->fetchAll();
    }

    /** @param array<string,mixed> $data */
    public function create(string $name, array $data = []): Club
    {
        $id = Uuid::uuid4()->toString();
        $stmt = $this->pdo()->prepare(
            'INSERT INTO clubs (id, name, siret, address, postal_code, city, country, area_sqm, opening_year, status)
             VALUES (:id, :name, :siret, :address, :postal, :city, :country, :area, :year, :status)'
        );
        $stmt->execute([
            ':id' => $id,
            ':name' => $name,
            ':siret' => $data['siret'] ?? null,
            ':address' => $data['address'] ?? null,
            ':postal' => $data['postal_code'] ?? null,
            ':city' => $data['city'] ?? null,
            ':country' => $data['country'] ?? 'France',
            ':area' => $data['area_sqm'] ?? null,
            ':year' => $data['opening_year'] ?? null,
            ':status' => 'active',
        ]);
        $club = $this->findById($id);
        if ($club === null) {
            throw new \RuntimeException('Création club échouée.');
        }
        return $club;
    }

    /** @param array<string,mixed> $data */
    public function update(string $id, string $name, array $data): void
    {
        $stmt = $this->pdo()->prepare(
            'UPDATE clubs SET name = :name, siret = :siret, address = :address, postal_code = :postal,
                    city = :city, country = :country, area_sqm = :area, opening_year = :year, updated_at = NOW()
             WHERE id = :id'
        );
        $stmt->execute([
            ':name' => $name,
            ':siret' => $data['siret'] ?? null,
            ':address' => $data['address'] ?? null,
            ':postal' => $data['postal_code'] ?? null,
            ':city' => $data['city'] ?? null,
            ':country' => $data['country'] ?? 'France',
            ':area' => $data['area_sqm'] ?? null,
            ':year' => $data['opening_year'] ?? null,
            ':id' => $id,
        ]);
    }

    /** @param string $status 'active' | 'suspended' | 'closed' */
    public function setStatus(string $clubId, string $status): void
    {
        $stmt = $this->pdo()->prepare('UPDATE clubs SET status = :status, updated_at = NOW() WHERE id = :id');
        $stmt->execute([':status' => $status, ':id' => $clubId]);
    }

    public function delete(string $id): void
    {
        $this->pdo()->prepare('DELETE FROM employees WHERE club_id = :id')->execute([':id' => $id]);
        $this->pdo()->prepare('DELETE FROM club_managers WHERE club_id = :id')->execute([':id' => $id]);
        $this->pdo()->prepare('DELETE FROM users WHERE club_id = :id')->execute([':id' => $id]);
        $this->pdo()->prepare('DELETE FROM clubs WHERE id = :id')->execute([':id' => $id]);
    }
}
