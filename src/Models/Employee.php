<?php

declare(strict_types=1);

namespace App\Models;

final class Employee
{
    public function __construct(
        public string $id,
        public string $clubId,
        public string $firstName,
        public string $lastName,
        public ?string $email,
        public ?string $jobTitle,
        public string $createdAt,
        public string $updatedAt,
    ) {
    }

    public static function fromRow(array $row): self
    {
        return new self(
            id: $row['id'],
            clubId: $row['club_id'],
            firstName: $row['first_name'],
            lastName: $row['last_name'],
            email: $row['email'] ?? null,
            jobTitle: $row['job_title'] ?? null,
            createdAt: $row['created_at'],
            updatedAt: $row['updated_at'],
        );
    }

    public function fullName(): string
    {
        return trim($this->firstName . ' ' . $this->lastName);
    }
}
