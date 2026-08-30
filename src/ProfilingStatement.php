<?php

declare(strict_types=1);

namespace App;

use App\Helpers\Profiler;
use PDOStatement;

/**
 * PDOStatement instrumenté : chronomètre execute() et enregistre la requête
 * dans le Profiler. Branché via PDO::ATTR_STATEMENT_CLASS uniquement en debug.
 */
final class ProfilingStatement extends PDOStatement
{
    protected function __construct() {}

    public function execute(?array $params = null): bool
    {
        $t = microtime(true);
        $ok = parent::execute($params);
        $elapsed = microtime(true) - $t;

        $rows = null;
        try {
            $rows = $this->rowCount();
        } catch (\Throwable) {
            // rowCount indispo sur certains SELECT selon le driver : on ignore.
        }
        Profiler::logQuery($this->queryString, $elapsed, $params ?? [], $rows);

        return $ok;
    }
}
