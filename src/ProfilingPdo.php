<?php

declare(strict_types=1);

namespace App;

use App\Helpers\Profiler;
use PDO;
use PDOStatement;

/**
 * PDO instrumenté : chronomètre query() et exec() (les prepare()->execute()
 * sont mesurés par ProfilingStatement). Utilisé à la place de PDO en debug.
 */
final class ProfilingPdo extends PDO
{
    public function query(string $query, ?int $fetchMode = null, mixed ...$fetchModeArgs): PDOStatement|false
    {
        $t = microtime(true);
        $stmt = $fetchMode === null
            ? parent::query($query)
            : parent::query($query, $fetchMode, ...$fetchModeArgs);
        $elapsed = microtime(true) - $t;

        $rows = null;
        if ($stmt instanceof PDOStatement) {
            try {
                $rows = $stmt->rowCount();
            } catch (\Throwable) {
            }
        }
        Profiler::logQuery($query, $elapsed, [], $rows);

        return $stmt;
    }

    public function exec(string $statement): int|false
    {
        $t = microtime(true);
        $result = parent::exec($statement);
        $elapsed = microtime(true) - $t;
        Profiler::logQuery($statement, $elapsed, [], is_int($result) ? $result : null);

        return $result;
    }
}
