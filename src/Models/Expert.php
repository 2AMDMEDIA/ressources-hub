<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Membre de la page « Nos experts » : soit un fondateur (kind=founder),
 * soit un membre de l'équipe / consultant (kind=team). Mêmes champs pour les deux.
 */
final class Expert
{
    public const KIND_FOUNDER = 'founder';
    public const KIND_TEAM = 'team';

    public function __construct(
        public string $id,
        public string $kind,
        public ?string $title,
        public string $name,
        public ?string $role,
        public ?string $bio,
        public ?string $photoUrl,
        public ?string $phone,
        public ?string $email,
        public ?string $accent,
        public int $position,
        public string $createdAt,
        public string $updatedAt,
    ) {
    }

    public static function fromRow(array $row): self
    {
        return new self(
            id: $row['id'],
            kind: $row['kind'] ?? self::KIND_TEAM,
            title: $row['title'] ?? null,
            name: $row['name'],
            role: $row['role'] ?? null,
            bio: $row['bio'] ?? null,
            photoUrl: $row['photo_url'] ?? null,
            phone: $row['phone'] ?? null,
            email: $row['email'] ?? null,
            accent: $row['accent'] ?? null,
            position: (int) ($row['position'] ?? 0),
            createdAt: $row['created_at'],
            updatedAt: $row['updated_at'],
        );
    }

    public function isFounder(): bool
    {
        return $this->kind === self::KIND_FOUNDER;
    }

    public function hasPhoto(): bool
    {
        return $this->photoUrl !== null && $this->photoUrl !== '';
    }

    /** Initiales pour l'avatar de repli quand il n'y a pas de photo. */
    public function initials(): string
    {
        $parts = preg_split('/\s+/', trim($this->name)) ?: [];
        $letters = '';
        foreach ($parts as $p) {
            if ($p !== '') { $letters .= mb_strtoupper(mb_substr($p, 0, 1)); }
            if (mb_strlen($letters) >= 2) { break; }
        }
        return $letters !== '' ? $letters : mb_strtoupper(mb_substr($this->name, 0, 1));
    }

    public function accentClass(): string
    {
        return in_array($this->accent, ['steel', 'navy', 'orange'], true) ? $this->accent : 'steel';
    }
}
