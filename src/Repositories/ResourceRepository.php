<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Database;
use App\Models\Resource;
use PDO;
use Ramsey\Uuid\Uuid;

final class ResourceRepository
{
    private function pdo(): PDO
    {
        return Database::pdo();
    }

    public function findById(string $id): ?Resource
    {
        $stmt = $this->pdo()->prepare('SELECT * FROM resources WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ? Resource::fromRow($row) : null;
    }

    /**
     * Liste des ressources avec le nom de leur catégorie (option : filtrer par catégorie).
     * @return list<array<string,mixed>>
     */
    public function listWithCategory(?string $categoryId = null): array
    {
        $sql = 'SELECT r.*, c.name AS category_name
                FROM resources r
                LEFT JOIN categories c ON c.id = r.category_id';
        $params = [];
        if ($categoryId !== null && $categoryId !== '') {
            $sql .= ' WHERE r.category_id = :cat';
            $params[':cat'] = $categoryId;
        }
        $sql .= ' ORDER BY r.created_at DESC';
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** @param array<string,mixed> $d */
    public function create(array $d): string
    {
        $id = Uuid::uuid4()->toString();
        $published = ($d['status'] ?? 'draft') === 'published';
        $stmt = $this->pdo()->prepare(
            'INSERT INTO resources
                (id, category_id, title, slug, description, format, level, video_provider, video_id,
                 video_duration, thumbnail_url, file_path, file_name, status, is_spotlight, published_at, created_by)
             VALUES
                (:id, :cat, :title, :slug, :desc, :format, :level, :vprov, :vid,
                 :vdur, :thumb, :fpath, :fname, :status, :spot, :pub, :by)'
        );
        $stmt->execute([
            ':id' => $id,
            ':cat' => $d['category_id'] ?: null,
            ':title' => $d['title'],
            ':slug' => $d['slug'] ?? null,
            ':desc' => $d['description'] ?: null,
            ':format' => $d['format'],
            ':level' => $d['level'] ?: null,
            ':vprov' => $d['video_provider'] ?: null,
            ':vid' => $d['video_id'] ?: null,
            ':vdur' => $d['video_duration'] ?: null,
            ':thumb' => $d['thumbnail_url'] ?: null,
            ':fpath' => ($d['file_path'] ?? null) ?: null,
            ':fname' => ($d['file_name'] ?? null) ?: null,
            ':status' => $d['status'] ?? 'draft',
            ':spot' => !empty($d['is_spotlight']) ? 1 : 0,
            ':pub' => $published ? date('Y-m-d H:i:s') : null,
            ':by' => $d['created_by'] ?? null,
        ]);
        return $id;
    }

    /** @param array<string,mixed> $d */
    public function update(string $id, array $d): void
    {
        // published_at : posé au premier passage en "published", conservé ensuite.
        $current = $this->findById($id);
        $pub = $current?->publishedAt;
        if (($d['status'] ?? 'draft') === 'published' && $pub === null) {
            $pub = date('Y-m-d H:i:s');
        }

        $sets = 'category_id = :cat, title = :title, slug = :slug, description = :desc, format = :format,
                 level = :level, video_provider = :vprov, video_id = :vid, video_duration = :vdur,
                 thumbnail_url = :thumb, status = :status, is_spotlight = :spot, published_at = :pub, updated_at = NOW()';
        $params = [
            ':cat' => $d['category_id'] ?: null,
            ':title' => $d['title'],
            ':slug' => $d['slug'] ?? null,
            ':desc' => $d['description'] ?: null,
            ':format' => $d['format'],
            ':level' => $d['level'] ?: null,
            ':vprov' => $d['video_provider'] ?: null,
            ':vid' => $d['video_id'] ?: null,
            ':vdur' => $d['video_duration'] ?: null,
            ':thumb' => $d['thumbnail_url'] ?: null,
            ':status' => $d['status'] ?? 'draft',
            ':spot' => !empty($d['is_spotlight']) ? 1 : 0,
            ':pub' => $pub,
            ':id' => $id,
        ];
        // Le fichier n'est mis à jour que s'il a été remplacé.
        if (array_key_exists('file_path', $d)) {
            $sets .= ', file_path = :fpath, file_name = :fname';
            $params[':fpath'] = $d['file_path'] ?: null;
            $params[':fname'] = $d['file_name'] ?: null;
        }

        $stmt = $this->pdo()->prepare("UPDATE resources SET $sets WHERE id = :id");
        $stmt->execute($params);
    }

    public function setStatus(string $id, string $status): void
    {
        $pub = $status === 'published' ? date('Y-m-d H:i:s') : null;
        // Ne pas écraser une date de publication existante.
        if ($status === 'published') {
            $stmt = $this->pdo()->prepare(
                'UPDATE resources SET status = :s, published_at = COALESCE(published_at, :p), updated_at = NOW() WHERE id = :id'
            );
            $stmt->execute([':s' => $status, ':p' => $pub, ':id' => $id]);
        } else {
            $stmt = $this->pdo()->prepare('UPDATE resources SET status = :s, updated_at = NOW() WHERE id = :id');
            $stmt->execute([':s' => $status, ':id' => $id]);
        }
    }

    public function setSpotlight(string $id, bool $on): void
    {
        $stmt = $this->pdo()->prepare('UPDATE resources SET is_spotlight = :v, updated_at = NOW() WHERE id = :id');
        $stmt->execute([':v' => $on ? 1 : 0, ':id' => $id]);
    }

    public function delete(string $id): void
    {
        $this->pdo()->prepare('DELETE FROM resource_segments WHERE resource_id = :id')->execute([':id' => $id]);
        $this->pdo()->prepare('DELETE FROM resources WHERE id = :id')->execute([':id' => $id]);
    }
}
