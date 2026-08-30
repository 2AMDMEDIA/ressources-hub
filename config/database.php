<?php

declare(strict_types=1);

return [
    'driver' => 'mysql',
    'host' => $_ENV['DB_HOST'] ?? 'localhost',
    'port' => (int) ($_ENV['DB_PORT'] ?? 3306),
    'name' => $_ENV['DB_NAME'] ?? '',
    'user' => $_ENV['DB_USER'] ?? '',
    'pass' => $_ENV['DB_PASS'] ?? '',
    'charset' => $_ENV['DB_CHARSET'] ?? 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_PERSISTENT => false,
        // Timeout de connexion (secondes). Sans ça, un DB_HOST injoignable
        // (mauvais hôte / pare-feu) fait attendre ~127 s le timeout TCP du système
        // et sature les workers PHP → tout le site rame. On échoue vite à la place.
        PDO::ATTR_TIMEOUT => (int) ($_ENV['DB_TIMEOUT'] ?? 5),
    ],
];
