<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Database;
use PDO;
use Ramsey\Uuid\Uuid;

final class ContactMessageRepository
{
    private function pdo(): PDO
    {
        return Database::pdo();
    }

    /** @param array{name:string,email:string,phone:?string,club:?string,subject:?string,message:string,ip:?string} $data */
    public function create(array $data): string
    {
        $id = Uuid::uuid4()->toString();
        $stmt = $this->pdo()->prepare(
            'INSERT INTO contact_messages (id, name, email, phone, club, subject, message, ip)
             VALUES (:id, :name, :email, :phone, :club, :subject, :message, :ip)'
        );
        $stmt->execute([
            ':id' => $id,
            ':name' => $data['name'],
            ':email' => $data['email'],
            ':phone' => $data['phone'] ?? null,
            ':club' => $data['club'] ?? null,
            ':subject' => $data['subject'] ?? null,
            ':message' => $data['message'],
            ':ip' => $data['ip'] ?? null,
        ]);
        return $id;
    }
}
