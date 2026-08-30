<?php

declare(strict_types=1);

namespace App\Helpers;

/**
 * Profiler de dev : mesure le temps total de la requête, le temps et le nombre
 * de requêtes SQL, la mémoire et les fichiers inclus, puis rend une barre de
 * debug en bas de page.
 *
 * Actif uniquement quand APP_DEBUG=true (enable() appelé au boot). En prod la
 * barre n'est jamais rendue et l'instrumentation PDO n'est pas branchée.
 */
final class Profiler
{
    private static bool $enabled = false;
    private static float $t0 = 0.0;
    /** @var list<array{sql:string,ms:float,params:array,rows:?int}> */
    private static array $queries = [];
    private static float $dbSeconds = 0.0;

    public static function enable(): void
    {
        self::$enabled = true;
        // Point de départ = arrivée réelle de la requête si dispo, sinon maintenant.
        self::$t0 = (float) ($_SERVER['REQUEST_TIME_FLOAT'] ?? microtime(true));
    }

    public static function enabled(): bool
    {
        return self::$enabled;
    }

    /** Enregistre une requête SQL exécutée (appelé par le PDO instrumenté). */
    public static function logQuery(string $sql, float $seconds, array $params = [], ?int $rows = null): void
    {
        if (!self::$enabled) {
            return;
        }
        self::$dbSeconds += $seconds;
        self::$queries[] = [
            'sql' => trim(preg_replace('/\s+/', ' ', $sql) ?? $sql),
            'ms' => $seconds * 1000,
            'params' => $params,
            'rows' => $rows,
        ];
    }

    /**
     * Émet la barre en fin de requête (fonction de shutdown). Ne fait rien si :
     * désactivé, exécution CLI, réponse non-HTML (JSON…) ou redirection.
     */
    public static function flush(): void
    {
        if (!self::$enabled || PHP_SAPI === 'cli') {
            return;
        }
        $status = http_response_code() ?: 200;
        if ($status >= 300 && $status < 400) {
            return; // redirection
        }
        foreach (headers_list() as $h) {
            $l = strtolower($h);
            if (str_starts_with($l, 'location:')) {
                return; // redirection
            }
            if (str_starts_with($l, 'content-type:') && !str_contains($l, 'text/html')) {
                return; // JSON, téléchargement, etc.
            }
        }
        echo self::renderBar();
    }

    /** Rend la barre de debug (HTML). Vide si le profiler est désactivé. */
    public static function renderBar(): string
    {
        if (!self::$enabled) {
            return '';
        }

        $totalMs = (microtime(true) - self::$t0) * 1000;
        $dbMs = self::$dbSeconds * 1000;
        $phpMs = max(0.0, $totalMs - $dbMs);
        $nbQueries = count(self::$queries);
        $memMb = memory_get_usage(true) / 1048576;
        $peakMb = memory_get_peak_usage(true) / 1048576;
        $nbFiles = count(get_included_files());
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $status = http_response_code() ?: 200;

        $e = static fn(string $s): string => htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $fmt = static fn(float $ms): string => number_format($ms, 1, ',', ' ');
        // Jeton CSRF pour la croix de fermeture (désactive le mode debug). Le
        // bouton n'apparaît que pour un super-admin (qui seul a pu activer la barre).
        $csrf = \App\Helpers\Csrf::token();
        $canClose = (bool) \App\Session::get('is_super_admin', false);

        // Détail des requêtes (repliable). Les requêtes lentes (>20 ms) sont mises en évidence.
        $rows = '';
        foreach (self::$queries as $i => $q) {
            $slow = $q['ms'] >= 20 ? ' rhp-q--slow' : '';
            $params = $q['params'] !== [] ? $e(self::paramsLabel($q['params'])) : '';
            $rowsInfo = $q['rows'] !== null ? $q['rows'] . ' lignes' : '';
            $rows .= '<tr class="rhp-q' . $slow . '">'
                . '<td class="rhp-q__n">' . ($i + 1) . '</td>'
                . '<td class="rhp-q__t">' . $fmt($q['ms']) . ' ms</td>'
                . '<td class="rhp-q__sql"><code>' . $e($q['sql']) . '</code>'
                . ($params !== '' ? '<span class="rhp-q__params">' . $params . '</span>' : '')
                . '</td>'
                . '<td class="rhp-q__rows">' . $e($rowsInfo) . '</td>'
                . '</tr>';
        }
        if ($rows === '') {
            $rows = '<tr><td colspan="4" class="rhp-q__empty">Aucune requête SQL sur cette page.</td></tr>';
        }

        ob_start(); ?>
<style>
#rhp-bar{position:fixed;left:0;right:0;bottom:0;z-index:2147483000;font:12px/1.4 ui-monospace,SFMono-Regular,Menlo,Consolas,monospace;color:#e6edf3;background:#12141a;border-top:2px solid #f97316;box-shadow:0 -4px 20px rgba(0,0,0,.35)}
#rhp-bar *{box-sizing:border-box}
#rhp-bar__strip{display:flex;align-items:center;gap:18px;padding:7px 14px;cursor:pointer;flex-wrap:wrap;user-select:none}
#rhp-bar__strip:hover{background:#171a22}
.rhp-m{display:inline-flex;align-items:baseline;gap:6px;white-space:nowrap}
.rhp-m b{font-size:13px;color:#fff;font-weight:600}
.rhp-m span{color:#8b98a8}
.rhp-m--total b{color:#f97316}
.rhp-m--db b{color:#38bdf8}
.rhp-badge{margin-left:auto;color:#8b98a8;font-size:11px}
#rhp-bar__toggle{margin-left:8px;color:#8b98a8}
.rhp-off{margin:0}
.rhp-off button{background:transparent;border:1px solid #3a4150;color:#8b98a8;border-radius:5px;padding:2px 8px;font:inherit;cursor:pointer;line-height:1}
.rhp-off button:hover{border-color:#f87171;color:#f87171}
#rhp-panel{display:none;max-height:45vh;overflow:auto;border-top:1px solid #262b36;background:#0d0f14}
#rhp-bar.is-open #rhp-panel{display:block}
#rhp-bar.is-open #rhp-bar__toggle::after{content:'▾'}
#rhp-bar__toggle::after{content:'▴'}
.rhp-tbl{width:100%;border-collapse:collapse}
.rhp-tbl th{position:sticky;top:0;background:#12141a;text-align:left;padding:6px 10px;color:#8b98a8;font-weight:600;border-bottom:1px solid #262b36}
.rhp-tbl td{padding:6px 10px;border-bottom:1px solid #1b1f28;vertical-align:top}
.rhp-q__n{color:#5b6675;text-align:right;width:34px}
.rhp-q__t{color:#38bdf8;white-space:nowrap;width:80px}
.rhp-q__rows{color:#8b98a8;white-space:nowrap;width:80px}
.rhp-q__sql code{color:#e6edf3;word-break:break-word}
.rhp-q__params{display:block;margin-top:3px;color:#f59e0b;font-size:11px}
.rhp-q--slow .rhp-q__t{color:#f87171;font-weight:700}
.rhp-q--slow .rhp-q__sql code{color:#fda4af}
.rhp-q__empty{color:#8b98a8;text-align:center;padding:14px}
@media print{#rhp-bar{display:none}}
</style>
<div id="rhp-bar">
    <div id="rhp-bar__strip" onclick="this.parentNode.classList.toggle('is-open')">
        <span class="rhp-m rhp-m--total"><b><?= $fmt($totalMs) ?> ms</b><span>total</span></span>
        <span class="rhp-m rhp-m--db"><b><?= $nbQueries ?></b><span>SQL ·</span><b><?= $fmt($dbMs) ?> ms</b></span>
        <span class="rhp-m"><b><?= $fmt($phpMs) ?> ms</b><span>PHP</span></span>
        <span class="rhp-m"><b><?= number_format($memMb, 1, ',', ' ') ?> Mo</b><span>mém. (pic <?= number_format($peakMb, 1, ',', ' ') ?>)</span></span>
        <span class="rhp-m"><b><?= $nbFiles ?></b><span>fichiers</span></span>
        <span class="rhp-badge"><?= $e((string) $method) ?> <?= (int) $status ?> · <?= $e((string) $uri) ?></span>
        <span id="rhp-bar__toggle"></span>
        <?php if ($canClose): ?>
        <form method="post" action="/admin/settings/debug-bar" class="rhp-off" onclick="event.stopPropagation()" title="Désactiver le mode debug">
            <input type="hidden" name="_csrf" value="<?= $e($csrf) ?>">
            <button type="submit" aria-label="Désactiver le mode debug">✕</button>
        </form>
        <?php endif; ?>
    </div>
    <div id="rhp-panel">
        <table class="rhp-tbl">
            <thead><tr><th style="text-align:right">#</th><th>Temps</th><th>Requête SQL (<?= $nbQueries ?>)</th><th>Résultat</th></tr></thead>
            <tbody><?= $rows ?></tbody>
        </table>
    </div>
</div>
<?php
        return (string) ob_get_clean();
    }

    /** Représentation compacte des paramètres liés, pour l'affichage. */
    private static function paramsLabel(array $params): string
    {
        $parts = [];
        foreach ($params as $k => $v) {
            $key = is_int($k) ? '#' . ($k + 1) : (string) $k;
            if (is_bool($v)) {
                $val = $v ? 'true' : 'false';
            } elseif ($v === null) {
                $val = 'null';
            } else {
                $val = (string) $v;
                if (mb_strlen($val) > 60) {
                    $val = mb_substr($val, 0, 57) . '…';
                }
            }
            $parts[] = $key . '=' . $val;
        }
        return implode('  ', $parts);
    }
}
