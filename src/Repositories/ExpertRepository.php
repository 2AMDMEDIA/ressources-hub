<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Database;
use App\Models\Expert;
use PDO;
use Ramsey\Uuid\Uuid;

final class ExpertRepository
{
    private function pdo(): PDO
    {
        return Database::pdo();
    }

    /** @return list<Expert> Membres d'un type donné, ordonnés. */
    public function listByKind(string $kind): array
    {
        $stmt = $this->pdo()->prepare(
            'SELECT * FROM experts WHERE kind = :k ORDER BY position ASC, created_at ASC'
        );
        $stmt->execute([':k' => $kind]);
        return array_map([Expert::class, 'fromRow'], $stmt->fetchAll());
    }

    public function findById(string $id): ?Expert
    {
        $stmt = $this->pdo()->prepare('SELECT * FROM experts WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ? Expert::fromRow($row) : null;
    }

    /**
     * @param array<string,mixed> $data clés: kind, name, role, bio, photo_url, phone, email, accent, position
     */
    public function create(array $data): Expert
    {
        $id = Uuid::uuid4()->toString();
        $kind = ($data['kind'] ?? Expert::KIND_TEAM) === Expert::KIND_FOUNDER
            ? Expert::KIND_FOUNDER : Expert::KIND_TEAM;
        $position = isset($data['position']) ? (int) $data['position'] : $this->nextPosition($kind);

        $stmt = $this->pdo()->prepare(
            'INSERT INTO experts (id, kind, name, role, bio, photo_url, phone, email, accent, position)
             VALUES (:id, :kind, :name, :role, :bio, :photo, :phone, :email, :accent, :pos)'
        );
        $stmt->execute([
            ':id' => $id,
            ':kind' => $kind,
            ':name' => (string) ($data['name'] ?? ''),
            ':role' => ($data['role'] ?? null) ?: null,
            ':bio' => ($data['bio'] ?? null) ?: null,
            ':photo' => ($data['photo_url'] ?? null) ?: null,
            ':phone' => ($data['phone'] ?? null) ?: null,
            ':email' => ($data['email'] ?? null) ?: null,
            ':accent' => ($data['accent'] ?? null) ?: null,
            ':pos' => $position,
        ]);

        $e = $this->findById($id);
        if ($e === null) {
            throw new \RuntimeException('Création expert échouée.');
        }
        return $e;
    }

    /**
     * Met à jour un expert. La photo n'est modifiée que si la clé photo_url est fournie.
     * @param array<string,mixed> $data
     */
    public function update(string $id, array $data): void
    {
        $sets = 'kind = :kind, name = :name, role = :role, bio = :bio,
                 phone = :phone, email = :email, accent = :accent, position = :pos, updated_at = NOW()';
        $params = [
            ':kind' => ($data['kind'] ?? Expert::KIND_TEAM) === Expert::KIND_FOUNDER
                ? Expert::KIND_FOUNDER : Expert::KIND_TEAM,
            ':name' => (string) ($data['name'] ?? ''),
            ':role' => ($data['role'] ?? null) ?: null,
            ':bio' => ($data['bio'] ?? null) ?: null,
            ':phone' => ($data['phone'] ?? null) ?: null,
            ':email' => ($data['email'] ?? null) ?: null,
            ':accent' => ($data['accent'] ?? null) ?: null,
            ':pos' => (int) ($data['position'] ?? 0),
            ':id' => $id,
        ];
        if (array_key_exists('photo_url', $data)) {
            $sets .= ', photo_url = :photo';
            $params[':photo'] = $data['photo_url'] ?: null;
        }
        $stmt = $this->pdo()->prepare("UPDATE experts SET $sets WHERE id = :id");
        $stmt->execute($params);
    }

    public function delete(string $id): void
    {
        $this->pdo()->prepare('DELETE FROM experts WHERE id = :id')->execute([':id' => $id]);
    }

    private function nextPosition(string $kind): int
    {
        $stmt = $this->pdo()->prepare(
            'SELECT COALESCE(MAX(position), -1) + 1 FROM experts WHERE kind = :k'
        );
        $stmt->execute([':k' => $kind]);
        return (int) $stmt->fetchColumn();
    }
}
