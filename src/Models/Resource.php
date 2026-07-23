<?php

declare(strict_types=1);

namespace App\Models;

final class Resource
{
    /** @var array<string,string> */
    public const FORMATS = [
        'video' => 'Vidéo formation',
        'replay_live' => 'Replay live / visio',
        'masterclass' => 'Masterclass',
        'pdf' => 'Fiche PDF',
        'template' => 'Modèle / template',
        'podcast' => 'Podcast / audio',
    ];

    /** @var array<string,string> */
    public const LEVELS = [
        'fondamentaux' => 'Fondamentaux',
        'avance' => 'Avancé',
    ];

    public function __construct(
        public string $id,
        public ?string $categoryId,
        public string $title,
        public ?string $slug,
        public ?string $description,
        public string $format,
        public ?string $level,
        public ?string $videoProvider,
        public ?string $videoId,
        public ?int $videoDuration,
        public ?string $thumbnailUrl,
        public ?string $filePath,
        public ?string $fileName,
        public string $status,
        public bool $isSpotlight,
        public ?string $publishedAt,
        public string $createdAt,
        public string $updatedAt,
    ) {
    }

    public static function fromRow(array $row): self
    {
        return new self(
            id: $row['id'],
            categoryId: $row['category_id'] ?? null,
            title: $row['title'],
            slug: $row['slug'] ?? null,
            description: $row['description'] ?? null,
            format: $row['format'] ?? 'video',
            level: $row['level'] ?? null,
            videoProvider: $row['video_provider'] ?? null,
            videoId: $row['video_id'] ?? null,
            videoDuration: isset($row['video_duration']) ? (int) $row['video_duration'] : null,
            thumbnailUrl: $row['thumbnail_url'] ?? null,
            filePath: $row['file_path'] ?? null,
            fileName: $row['file_name'] ?? null,
            status: $row['status'] ?? 'draft',
            isSpotlight: (bool) ($row['is_spotlight'] ?? false),
            publishedAt: $row['published_at'] ?? null,
            createdAt: $row['created_at'],
            updatedAt: $row['updated_at'],
        );
    }

    public function formatLabel(): string
    {
        return self::FORMATS[$this->format] ?? $this->format;
    }

    public function levelLabel(): ?string
    {
        return $this->level !== null ? (self::LEVELS[$this->level] ?? $this->level) : null;
    }

    public function isVideo(): bool
    {
        return in_array($this->format, ['video', 'replay_live', 'masterclass'], true);
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }
}
