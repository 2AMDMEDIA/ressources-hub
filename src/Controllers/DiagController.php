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

        // --- Test SMTP / email ---
        $lines[] = '';
        $lines[] = '=== SMTP / Email ===';
        $host = (string) ($_ENV['MAIL_HOST'] ?? '');
        $port = (string) ($_ENV['MAIL_PORT'] ?? '587');
        $enc  = strtolower((string) ($_ENV['MAIL_ENCRYPTION'] ?? 'tls'));
        $user = (string) ($_ENV['MAIL_USER'] ?? '');
        $from = (string) ($_ENV['MAIL_FROM_EMAIL'] ?? '');
        if ($host === '') {
            $lines[] = 'MAIL_HOST vide → aucun envoi (emails loggés dans storage/logs/mail.log).';
        } else {
            $lines[] = 'Hôte       : ' . $host . ':' . $port . ' (' . $enc . ')';
            $lines[] = 'User       : ' . ($user !== '' ? mb_substr($user, 0, 3) . '***' : '(vide)');
            $lines[] = 'From       : ' . $from;
            $mailto = (string) ($this->input('mailto') ?? '');
            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = $host;
                $mail->Port = (int) $port;
                $mail->SMTPAuth = $user !== '';
                $mail->Username = $user;
                $mail->Password = (string) ($_ENV['MAIL_PASS'] ?? '');
                $mail->CharSet = 'UTF-8';
                $mail->Timeout = 12;
                if ($enc === 'ssl') { $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS; }
                elseif ($enc === 'tls') { $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS; }

                $tm = microtime(true);
                if ($mailto !== '') {
                    $mail->setFrom($from ?: 'noreply@example.com', (string) ($_ENV['MAIL_FROM_NAME'] ?? 'RESSOURCES'));
                    $mail->addAddress($mailto);
                    $mail->Subject = 'Test SMTP RESSOURCES';
                    $mail->Body = 'Test SMTP OK — ' . date('c');
                    $mail->send();
                    $lines[] = sprintf('Envoi test : OK vers %s en %.0f ms', $mailto, (microtime(true) - $tm) * 1000);
                } else {
                    $ok = $mail->smtpConnect();
                    $lines[] = sprintf('Connexion+auth : %s en %.0f ms', $ok ? 'OK' : 'ECHEC', (microtime(true) - $tm) * 1000);
                    $mail->smtpClose();
                    $lines[] = '(ajoutez &mailto=votre@email pour un envoi réel de test)';
                }
            } catch (\Throwable $e) {
                $lines[] = 'SMTP : ECHEC — ' . $e->getMessage();
                if ($mail->ErrorInfo !== '') { $lines[] = 'Détail PHPMailer : ' . $mail->ErrorInfo; }
            }
        }

        $lines[] = '';
        $lines[] = 'Mémoire    : ' . round(memory_get_peak_usage(true) / 1048576, 1) . ' Mo';
        $lines[] = 'Temps PHP total (depuis arrivée requête) : '
            . sprintf('%.1f ms', (microtime(true) - (float) ($_SERVER['REQUEST_TIME_FLOAT'] ?? microtime(true))) * 1000);

        echo implode("\n", $lines) . "\n";
    }
}
