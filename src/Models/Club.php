<?php

declare(strict_types=1);

namespace App\Models;

final class Club
{
    public function __construct(
        public string $id,
        public string $name,
        public ?string $ownerUserId,
        public string $status,        // 'active' | 'suspended' | 'closed'
        public int $seatsLimit,
        public ?string $contactEmail,
        public ?string $contractRef,
        public string $createdAt,
        public string $updatedAt,
    ) {
    }

    public static function fromRow(array $row): self
    {
        return new self(
            id: $row['id'],
            name: $row['name'],
            ownerUserId: $row['owner_user_id'] ?? null,
            status: $row['status'] ?? 'active',
            seatsLimit: (int) ($row['seats_limit'] ?? 1),
            contactEmail: $row['contact_email'] ?? null,
            contractRef: $row['contract_ref'] ?? null,
            createdAt: $row['created_at'],
            updatedAt: $row['updated_at'],
        );
    }

    /** Le club donne-t-il accès à la bibliothèque ? (seul 'active' ouvre l'accès) */
    public function grantsAccess(): bool
    {
        return $this->status === 'active';
    }
}
