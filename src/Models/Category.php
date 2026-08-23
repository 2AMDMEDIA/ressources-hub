<?php

declare(strict_types=1);

namespace App\Models;

final class Category
{
    /** Image affichée par défaut si aucune miniature n'est uploadée. */
    public const DEFAULT_THUMB = '/assets/img/logo.png';

    public function __construct(
        public string $id,
        public ?string $parentId,
        public string $slug,
        public string $name,
        public ?string $shortDescription,
        public ?string $longDescription,
        public ?string $thumbnailUrl,
        public ?string $introVideoId,
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
            shortDescription: $row['short_description'] ?? null,
            longDescription: $row['long_description'] ?? null,
            thumbnailUrl: $row['thumbnail_url'] ?? null,
            introVideoId: $row['intro_video_id'] ?? null,
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

    /** Miniature à afficher : celle uploadée, sinon le logo RESSOURCES. */
    public function thumbnail(): string
    {
        return $this->thumbnailUrl !== null && $this->thumbnailUrl !== '' ? $this->thumbnailUrl : self::DEFAULT_THUMB;
    }
}
