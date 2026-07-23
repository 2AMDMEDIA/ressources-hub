<?php

declare(strict_types=1);

namespace App\Models;

final class Category
{
    public function __construct(
        public string $id,
        public ?string $parentId,
        public string $slug,
        public string $name,
        public int $position,
        public ?string $icon,
        public string $createdAt,
        public string $updatedAt,
    ) {
    }

    public static function fromRow(array $row): self
    {
        return new self(
            id: $row['id'],
            parentId: $row['parent_id'] ?? null,
            slug: $row['slug'],
            name: $row['name'],
            position: (int) ($row['position'] ?? 0),
            icon: $row['icon'] ?? null,
            createdAt: $row['created_at'],
            updatedAt: $row['updated_at'],
        );
    }

    public function isTopLevel(): bool
    {
        return $this->parentId === null;
    }
}
