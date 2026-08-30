<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Database;

/**
 * Endpoint de diagnostic de perf (temporaire, dev/support). Protégé par un token :
 * n'affiche rien si DIAG_TOKEN n'est pas défini dans .env ou si ?k= ne correspond pas.
 *
 * Mesure le temps de connexion à MySQL et d'une requête triviale — pour localiser
 * une lenteur (connexion DB qui traîne = symptôme classique d'un DB_HOST injoignable).
 */
final class DiagController extends BaseController
{
    public function index(): void
    {
        $expected = (string) ($_ENV['DIAG_TOKEN'] ?? '');
        $given = (string) ($this->input('k') ?? '');
        if ($expected === '' || !hash_equals($expected, $given)) {
            http_response_code(404);
            echo 'Not found';
            return;
        }

        header('Content-Type: text/plain; charset=utf-8');
        $lines = [];
        $lines[] = '=== Diagnostic RESSOURCES ===';
        $lines[] = 'PHP        : ' . PHP_VERSION . ' (' . PHP_SAPI . ')';
        $lines[] = 'DB hôte    : ' . ($_ENV['DB_HOST'] ?? '?') . ':' . ($_ENV['DB_PORT'] ?? '?');
        $lines[] = 'DB base    : ' . ($_ENV['DB_NAME'] ?? '?');
        $lines[] = 'DB timeout : ' . ($_ENV['DB_TIMEOUT'] ?? '5') . 's';

        // Connexion DB
        $t = microtime(true);
        try {
            $pdo = Database::pdo();
            $connMs = (microtime(true) - $t) * 1000;
            $lines[] = sprintf('Connexion DB : OK en %.1f ms', $connMs);

            // Requête triviale
            $t2 = microtime(true);
            $pdo->query('SELECT 1')->fetchColumn();
            $qMs = (microtime(true) - $t2) * 1000;
            $lines[] = sprintf('SELECT 1     : OK en %.1f ms', $qMs);

            // Compte de catégories (table réelle)
            $t3 = microtime(true);
            try {
                $n = $pdo->query('SELECT COUNT(*) FROM categories')->fetchColumn();
                $cMs = (microtime(true) - $t3) * 1000;
                $lines[] = sprintf('COUNT categories : %s en %.1f ms', (string) $n, $cMs);
            } catch (\Throwable $e) {
                $lines[] = 'COUNT categories : ERREUR — ' . $e->getMessage();
            }
        } catch (\Throwable $e) {
            $connMs = (microtime(true) - $t) * 1000;
            $lines[] = sprintf('Connexion DB : ECHEC après %.1f ms', $connMs);
            $lines[] = 'Raison       : ' . $e->getMessage();
        }

        $lines[] = 'Mémoire    : ' . round(memory_get_peak_usage(true) / 1048576, 1) . ' Mo';
        $lines[] = 'Temps PHP total (depuis arrivée requête) : '
            . sprintf('%.1f ms', (microtime(true) - (float) ($_SERVER['REQUEST_TIME_FLOAT'] ?? microtime(true))) * 1000);

        echo implode("\n", $lines) . "\n";
    }
}
