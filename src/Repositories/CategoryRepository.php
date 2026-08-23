<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Database;
use App\Models\Category;
use PDO;
use Ramsey\Uuid\Uuid;

final class CategoryRepository
{
    private function pdo(): PDO
    {
        return Database::pdo();
    }

    public function findById(string $id): ?Category
    {
        $stmt = $this->pdo()->prepare('SELECT * FROM categories WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ? Category::fromRow($row) : null;
    }

    /** @return list<Category> Catégories racines, ordonnées. */
    public function topLevel(): array
    {
        $rows = $this->pdo()->query(
            'SELECT * FROM categories WHERE parent_id IS NULL ORDER BY position ASC, name ASC'
        )->fetchAll();
        return array_map([Category::class, 'fromRow'], $rows);
    }

    /** @return list<Category> Toutes les catégories, ordonnées. */
    public function all(): array
    {
        $rows = $this->pdo()->query('SELECT * FROM categories ORDER BY position ASC, name ASC')->fetchAll();
        return array_map([Category::class, 'fromRow'], $rows);
    }

    public function findBySlug(string $slug): ?Category
    {
        $stmt = $this->pdo()->prepare('SELECT * FROM categories WHERE slug = :s LIMIT 1');
        $stmt->execute([':s' => $slug]);
        $row = $stmt->fetch();
        return $row ? Category::fromRow($row) : null;
    }

    /** @return list<Category> Sous-catégories d'un parent, ordonnées. */
    public function children(string $parentId): array
    {
        $stmt = $this->pdo()->prepare(
            'SELECT * FROM categories WHERE parent_id = :p ORDER BY position ASC, name ASC'
        );
        $stmt->execute([':p' => $parentId]);
        return array_map([Category::class, 'fromRow'], $stmt->fetchAll());
    }

    /**
     * Liste aplatie et indentée pour un menu déroulant — profondeur illimitée.
     * @return list<array{id:string,label:string,depth:int,is_child:bool}>
     */
    public function flatList(): array
    {
        $all = $this->all();
        $byParent = [];
        foreach ($all as $c) {
            $byParent[$c->parentId ?? ''][] = $c;
        }
        $out = [];
        $walk = function (?string $parentId, int $depth) use (&$walk, &$out, $byParent): void {
            foreach ($byParent[$parentId ?? ''] ?? [] as $cat) {
                $out[] = ['id' => $cat->id, 'label' => $cat->name, 'depth' => $depth, 'is_child' => $depth > 0];
                $walk($cat->id, $depth + 1);
            }
        };
        $walk(null, 0);
        return $out;
    }

    /**
     * Arbre imbriqué complet (profondeur illimitée).
     * @return list<array{cat:Category,children:array}>
     */
    public function tree(): array
    {
        $byParent = [];
        foreach ($this->all() as $c) {
            $byParent[$c->parentId ?? ''][] = $c;
        }
        $build = function (?string $parentId) use (&$build, $byParent): array {
            $nodes = [];
            foreach ($byParent[$parentId ?? ''] ?? [] as $cat) {
                $nodes[] = ['cat' => $cat, 'children' => $build($cat->id)];
            }
            return $nodes;
        };
        return $build(null);
    }

    public function countChildren(string $id): int
    {
        $stmt = $this->pdo()->prepare('SELECT COUNT(*) FROM categories WHERE parent_id = :p');
        $stmt->execute([':p' => $id]);
        return (int) $stmt->fetchColumn();
    }

    public function create(string $name, ?string $parentId = null): Category
    {
        $id = Uuid::uuid4()->toString();
        $slug = $this->uniqueSlug($this->slugify($name));
        $position = $this->nextPosition($parentId);
        $stmt = $this->pdo()->prepare(
            'INSERT INTO categories (id, parent_id, slug, name, position) VALUES (:id, :parent, :slug, :name, :pos)'
        );
        $stmt->execute([
            ':id' => $id,
            ':parent' => $parentId,
            ':slug' => $slug,
            ':name' => $name,
            ':pos' => $position,
        ]);
        $cat = $this->findById($id);
        if ($cat === null) {
            throw new \RuntimeException('Création catégorie échouée.');
        }
        return $cat;
    }

    /**
     * Met à jour une catégorie. Le slug reste stable au renommage.
     * @param array<string,mixed> $data clés: short_description, long_description, thumbnail_url, intro_video_id
     */
    public function update(string $id, string $name, int $position, array $data = []): void
    {
        $sets = 'name = :name, position = :pos, short_description = :short,
                 long_description = :long, intro_video_id = :video, updated_at = NOW()';
        $params = [
            ':name' => $name,
            ':pos' => $position,
            ':short' => ($data['short_description'] ?? null) ?: null,
            ':long' => ($data['long_description'] ?? null) ?: null,
            ':video' => ($data['intro_video_id'] ?? null) ?: null,
            ':id' => $id,
        ];
        // La miniature n'est mise à jour que si une nouvelle image a été fournie.
        if (array_key_exists('thumbnail_url', $data)) {
            $sets .= ', thumbnail_url = :thumb';
            $params[':thumb'] = $data['thumbnail_url'] ?: null;
        }
        $stmt = $this->pdo()->prepare("UPDATE categories SET $sets WHERE id = :id");
        $stmt->execute($params);
    }

    public function delete(string $id): void
    {
        $this->pdo()->prepare('DELETE FROM categories WHERE id = :id')->execute([':id' => $id]);
    }

    // ------------------------------------------------------------------ helpers

    private function nextPosition(?string $parentId): int
    {
        if ($parentId === null) {
            $stmt = $this->pdo()->query('SELECT COALESCE(MAX(position), -1) + 1 FROM categories WHERE parent_id IS NULL');
            return (int) $stmt->fetchColumn();
        }
        $stmt = $this->pdo()->prepare('SELECT COALESCE(MAX(position), -1) + 1 FROM categories WHERE parent_id = :p');
        $stmt->execute([':p' => $parentId]);
        return (int) $stmt->fetchColumn();
    }

    private function slugify(string $text): string
    {
        $map = ['à'=>'a','â'=>'a','ä'=>'a','á'=>'a','ã'=>'a','å'=>'a','ç'=>'c','é'=>'e','è'=>'e','ê'=>'e','ë'=>'e',
                'î'=>'i','ï'=>'i','í'=>'i','ì'=>'i','ô'=>'o','ö'=>'o','ó'=>'o','ò'=>'o','õ'=>'o','ù'=>'u','û'=>'u',
                'ü'=>'u','ú'=>'u','ÿ'=>'y','ñ'=>'n','œ'=>'oe','æ'=>'ae'];
        $text = strtr(mb_strtolower(trim($text)), $map);
        $text = preg_replace('/[^a-z0-9]+/', '-', $text) ?? '';
        $text = trim($text, '-');
        return $text !== '' ? $text : 'categorie';
    }

    private function uniqueSlug(string $base): string
    {
        $slug = $base;
        $i = 2;
        while ($this->slugExists($slug)) {
            $slug = $base . '-' . $i;
            $i++;
        }
        return $slug;
    }

    private function slugExists(string $slug): bool
    {
        $stmt = $this->pdo()->prepare('SELECT 1 FROM categories WHERE slug = :s LIMIT 1');
        $stmt->execute([':s' => $slug]);
        return (bool) $stmt->fetchColumn();
    }
}
