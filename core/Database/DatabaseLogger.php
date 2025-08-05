<?php

namespace Core\Database;

use Doctrine\DBAL\Logging\SQLLogger;

class DatabaseLogger implements SQLLogger
{
    public static array $queries = [];

    public function startQuery($sql, ?array $params = null, ?array $types = null): void
    {
        self::$queries[] = [
            'sql' => $sql,
            'params' => $params,
            'types' => $types,
            'start' => microtime(true),
            'end' => null,
            'time' => null,
            'rows' => null,
        ];
    }

    public function stopQuery(): void
    {
        $lastQueryIndex = count(self::$queries) - 1;
        if ($lastQueryIndex >= 0) {
            self::$queries[$lastQueryIndex]['end'] = microtime(true);
            self::$queries[$lastQueryIndex]['time'] = (self::$queries[$lastQueryIndex]['end'] - self::$queries[$lastQueryIndex]['start']) * 1000;
        }
    }

    public static function addRowsToLastQuery(int $rows): void
    {
        $lastQueryIndex = count(self::$queries) - 1;
        if ($lastQueryIndex >= 0) {
            self::$queries[$lastQueryIndex]['rows'] = $rows;
        }
    }
} 