<?php

declare(strict_types=1);

namespace App\Models;

final class Club
{
    public function __construct(
        public string $id,
        public string $name,
        public ?string $siret,
        public ?string $address,
        public ?string $postalCode,
        public ?string $city,
        public ?string $country,
        public ?int $areaSqm,
        public ?int $openingYear,
        public string $status,        // 'active' | 'suspended' | 'closed'
        public string $createdAt,
        public string $updatedAt,
    ) {
    }

    public static function fromRow(array $row): self
    {
        return new self(
            id: $row['id'],
            name: $row['name'],
            siret: $row['siret'] ?? null,
            address: $row['address'] ?? null,
            postalCode: $row['postal_code'] ?? null,
            city: $row['city'] ?? null,
            country: $row['country'] ?? null,
            areaSqm: isset($row['area_sqm']) ? (int) $row['area_sqm'] : null,
            openingYear: isset($row['opening_year']) ? (int) $row['opening_year'] : null,
            status: $row['status'] ?? 'active',
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
