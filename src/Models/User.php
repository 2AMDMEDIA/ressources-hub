<?php

declare(strict_types=1);

namespace App\Models;

final class User
{
    public function __construct(
        public string $id,
        public ?string $clubId,
        public string $email,
        public ?string $passwordHash,
        public string $fullName,
        public ?string $firstName,
        public ?string $lastName,
        public ?string $jobTitle,
        public string $role,          // 'super_admin' | 'club_owner' | 'club_member'
        public bool $isSuperAdmin,
        public string $status,        // 'active' | 'suspended'
        public bool $needsPasswordSetup,
        public ?string $lastLoginAt,
        public string $createdAt,
        public string $updatedAt,
    ) {
    }

    public static function fromRow(array $row): self
    {
        return new self(
            id: $row['id'],
            clubId: $row['club_id'] ?? null,
            email: $row['email'],
            passwordHash: $row['password_hash'],
            fullName: $row['full_name'] ?? '',
            firstName: $row['first_name'] ?? null,
            lastName: $row['last_name'] ?? null,
            jobTitle: $row['job_title'] ?? null,
            role: $row['role'] ?? 'club_member',
            isSuperAdmin: (bool) ($row['is_super_admin'] ?? false),
            status: $row['status'] ?? 'active',
            needsPasswordSetup: (bool) ($row['needs_password_setup'] ?? false),
            lastLoginAt: $row['last_login_at'] ?? null,
            createdAt: $row['created_at'],
            updatedAt: $row['updated_at'],
        );
    }

    public function verifyPassword(string $plain): bool
    {
        if ($this->passwordHash === null || $this->passwordHash === '') {
            return false;
        }
        return password_verify($plain, $this->passwordHash);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isClubOwner(): bool
    {
        return $this->role === 'club_owner';
    }

    public function displayName(): string
    {
        return $this->fullName !== '' ? $this->fullName : $this->email;
    }
}
