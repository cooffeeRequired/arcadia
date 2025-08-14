<?php

/**
 * Helper funkce pro aplikaci
 */

use Core\Facades\Container;
use Core\Notification\Toast;
use Core\Routing\Request;
use Random\RandomException;
use Symfony\Component\VarDumper\VarDumper;

if (!function_exists('request')) {
    /**
     * Získá aktuální HTTP request
     *
     * @return Request
     */
    function request(): Request
    {
        return Request::getInstance();
    }
}


function debug_log($message): void
{
    $logFile = APP_ROOT . '/log/debug.log';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}


if (!function_exists('e')) {
    /**
     * Escape HTML string
     *
     * @param string $value
     * @return string
     */
    function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8', false);
    }
}

if (!function_exists('asset')) {
    /**
     * Generuje URL pro asset soubory
     *
     * @param string $path
     * @return string
     */
    function asset(string $path): string
    {
        return '/assets/' . ltrim($path, '/');
    }
}

if (!function_exists('public_path')) {
    /**
     * Generuje URL pro asset soubory
     *
     * @param string $path
     * @return string
     */
    function public_path(string $path): string
    {
        return '/public/' . ltrim($path, '/');
    }
}

if (!function_exists('url')) {
    /**
     * Generuje URL pro danou cestu
     *
     * @param string $path
     * @return string
     */
    function url(string $path = ''): string
    {
        $base = rtrim($_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'], '/');
        return $base . '/' . ltrim($path, '/');
    }
}

if (!function_exists('old')) {
    /**
     * Získá starou hodnotu z formuláře
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function old(string $key, mixed $default = ''): mixed
    {
        return $_SESSION['old'][$key] ?? $default;
    }
}

if (!function_exists('session')) {
    /**
     * Získá nebo nastaví session hodnotu
     *
     * @param string|null $key
     * @param mixed|null $value
     * @return mixed
     */
    function session(?string $key = null, mixed $value = null): mixed
    {
        if ($key === null) {
            return $_SESSION;
        }

        if ($value === null) {
            return $_SESSION[$key] ?? null;
        }

        $_SESSION[$key] = $value;
        return $value;
    }
}

if (!function_exists('dump')) {
    /**
     * Dump proměnné pomocí Symfony Var Dumper
     *
     * @param mixed ...$vars
     * @return void
     */
    function dump(...$vars): void
    {
        if (class_exists('Symfony\Component\VarDumper\VarDumper')) {
            foreach ($vars as $var) {
                VarDumper::dump($var);
            }
        } else {
            // Fallback pro případ, že Symfony Var Dumper není dostupný
            foreach ($vars as $var) {
                var_dump($var);
            }
        }
    }
}

if (!function_exists('dd')) {
    /**
     * Dump proměnné a ukončí skript
     *
     * @param mixed ...$vars
     * @return never
     */
    function dd(...$vars): never
    {
        dump(...$vars);
        exit(1);
    }
}

if (!function_exists('clear_cache')) {
    /**
     * Vymaže všechny cache soubory (zachová složky)
     *
     * @return bool
     */
    function clear_cache(): bool
    {
        $cacheDir = APP_CONFIGURATION['cache_dir'];
        $success = true;

        // Funkce pro rekurzivní smazání pouze souborů (zachová složky)
        $deleteFilesOnly = function($dir) use (&$deleteFilesOnly, &$success) {
            if (!is_dir($dir)) {
                return;
            }

            $files = array_diff(scandir($dir), ['.', '..']);

            foreach ($files as $file) {
                $path = $dir . DIRECTORY_SEPARATOR . $file;

                if (is_dir($path)) {
                    // Rekurzivně projít složku, ale nesmazat ji
                    $deleteFilesOnly($path);
                } else {
                    // Smazat pouze soubory
                    if (!unlink($path)) {
                        $success = false;
                    }
                }
            }
        };

        // Vymazání cache souborů (zachová složky)
        if (is_dir($cacheDir)) {
            $deleteFilesOnly($cacheDir);
        }

        // Vymazání Redis cache
        try {
            $redis = new Predis\Client('tcp://127.0.0.1:6379');
            $redis->flushdb();
        } catch (Exception) {
            // Redis není dostupný, ignorujeme
        }

        // Pokročilé využití OPcache
        if (function_exists('opcache_reset')) {
            // Reset OPcache
            opcache_reset();

            // Pokud je OPcache dostupný, zkusíme invalidovat konkrétní soubory
            if (function_exists('opcache_invalidate')) {
                // Invalidovat všechny PHP soubory v cache složce
                $invalidatePhpFiles = function($dir) use (&$invalidatePhpFiles) {
                    if (!is_dir($dir)) {
                        return;
                    }

                    $files = array_diff(scandir($dir), ['.', '..']);

                    foreach ($files as $file) {
                        $path = $dir . DIRECTORY_SEPARATOR . $file;

                        if (is_dir($path)) {
                            $invalidatePhpFiles($path);
                        } elseif (pathinfo($path, PATHINFO_EXTENSION) === 'php') {
                            // Invalidovat PHP soubory v OPcache
                            opcache_invalidate($path, true);
                        }
                    }
                };

                $invalidatePhpFiles($cacheDir);
            }
        }

        return $success;
    }
}

if (!function_exists('optimize_opcache')) {
    /**
     * Optimalizuje OPcache pro lepší výkon
     *
     * @return bool
     */
    function optimize_opcache(): bool
    {
        if (!function_exists('opcache_compile_file')) {
            return false;
        }

        $appRoot = APP_ROOT;
        $success = true;

        // Funkce pro kompilaci PHP souborů do OPcache
        $compilePhpFiles = function($dir) use (&$compilePhpFiles, &$success, $appRoot) {
            if (!is_dir($dir)) {
                return;
            }

            $files = array_diff(scandir($dir), ['.', '..']);

            foreach ($files as $file) {
                $path = $dir . DIRECTORY_SEPARATOR . $file;

                if (is_dir($path)) {
                    $compilePhpFiles($path);
                } elseif (pathinfo($path, PATHINFO_EXTENSION) === 'php') {
                    // Kompilovat PHP soubory do OPcache
                    if (!opcache_compile_file($path)) {
                        $success = false;
                    }
                }
            }
        };

        // Kompilovat hlavní složky aplikace
        $directories = [
            $appRoot . '/app',
            $appRoot . '/core',
            $appRoot . '/config'
        ];

        foreach ($directories as $dir) {
            if (is_dir($dir)) {
                $compilePhpFiles($dir);
            }
        }

        return $success;
    }
}

if (!function_exists('get_opcache_status')) {
    /**
     * Získá informace o stavu OPcache
     *
     * @return array|null
     */
    function get_opcache_status(): ?array
    {
        if (!function_exists('opcache_get_status')) {
            return null;
        }

        return opcache_get_status(false);
    }
}

if (!function_exists('create_backup')) {
    /**
     * Vytvoří zálohu databáze a souborů
     *
     * @return bool
     */
    function create_backup(): bool
    {
        try {
            $backupDir = APP_ROOT . '/backups';
            if (!is_dir($backupDir)) {
                mkdir($backupDir, 0755, true);
            }

            $timestamp = date('Y-m-d_H-i-s');
            $backupPath = $backupDir . '/backup_' . $timestamp;

            if (!is_dir($backupPath)) {
                mkdir($backupPath, 0755, true);
            }

            // Záloha databáze
            $dbBackup = backup_database($backupPath);

            // Záloha souborů
            $filesBackup = backup_files($backupPath);

            // Vytvoření manifestu zálohy
            $manifest = [
                'created_at' => date('Y-m-d H:i:s'),
                'database' => $dbBackup,
                'files' => $filesBackup,
                'version' => '1.0',
                'app_name' => APP_CONFIGURATION['app_name'] ?? 'Arcadia CRM'
            ];

            file_put_contents($backupPath . '/manifest.json', json_encode($manifest, JSON_PRETTY_PRINT));

            return true;
        } catch (Exception $e) {
            error_log('Backup error: ' . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('backup_database')) {
    /**
     * Zálohuje databázi
     *
     * @param string $backupPath
     * @return array
     * @throws Exception
     */
    function backup_database(string $backupPath): array
    {
        $em = Container::get('doctrine.em');
        $connection = $em->getConnection();
        $params = $connection->getParams();

        $dbBackupFile = $backupPath . '/database.sql';

        // Pro MySQL/MariaDB
        if (isset($params['driver']) && $params['driver'] === 'pdo_mysql') {
            $host = $params['host'] ?? 'localhost';
            $port = $params['port'] ?? 3306;
            $dbname = $params['dbname'] ?? '';
            $user = $params['user'] ?? '';
            $password = $params['password'] ?? '';

            $command = sprintf(
                'mysqldump -h %s -P %d -u %s %s %s > %s',
                escapeshellarg($host),
                $port,
                escapeshellarg($user),
                $password ? '-p' . escapeshellarg($password) : '',
                escapeshellarg($dbname),
                escapeshellarg($dbBackupFile)
            );

            exec($command, $output, $returnCode);

            if ($returnCode !== 0) {
                throw new Exception('Database backup failed');
            }
        }

        return [
            'file' => basename($dbBackupFile),
            'size' => filesize($dbBackupFile),
            'created_at' => date('Y-m-d H:i:s')
        ];
    }
}

if (!function_exists('backup_files')) {
    /**
     * Zálohuje důležité soubory
     *
     * @param string $backupPath
     * @return array
     */
    function backup_files(string $backupPath): array
    {
        $filesBackupDir = $backupPath . '/files';
        if (!is_dir($filesBackupDir)) {
            mkdir($filesBackupDir, 0755, true);
        }

        $importantDirs = [
            'config' => APP_ROOT . '/config',
            'app' => APP_ROOT . '/app',
            'core' => APP_ROOT . '/core',
            'resources' => APP_ROOT . '/resources'
        ];

        $backupInfo = [];

        foreach ($importantDirs as $name => $dir) {
            if (is_dir($dir)) {
                $targetDir = $filesBackupDir . '/' . $name;
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0755, true);
                }

                // Kopírování souborů
                $iterator = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
                    RecursiveIteratorIterator::SELF_FIRST
                );

                $fileCount = 0;
                $totalSize = 0;

                foreach ($iterator as $file) {
                    if ($file->isFile()) {
                        $relativePath = str_replace($dir . '/', '', $file->getPathname());
                        $targetFile = $targetDir . '/' . $relativePath;

                        $targetDirPath = dirname($targetFile);
                        if (!is_dir($targetDirPath)) {
                            mkdir($targetDirPath, 0755, true);
                        }

                        if (copy($file->getPathname(), $targetFile)) {
                            $fileCount++;
                            $totalSize += $file->getSize();
                        }
                    }
                }

                $backupInfo[$name] = [
                    'files' => $fileCount,
                    'size' => $totalSize,
                    'created_at' => date('Y-m-d H:i:s')
                ];
            }
        }

        return $backupInfo;
    }
}

if (!function_exists('get_system_logs')) {
    /**
     * Získá systémové logy
     *
     * @param int $limit
     * @return array
     */
    function get_system_logs(int $limit = 100): array
    {
        $logs = [];

        // PHP error log
        $errorLog = ini_get('error_log');
        if ($errorLog && file_exists($errorLog)) {
            $logs['php_errors'] = [
                'name' => 'PHP Error Log',
                'file' => $errorLog,
                'entries' => get_log_entries($errorLog, $limit)
            ];
        }

        // Aplikace log
        $appLog = APP_ROOT . '/var/log/app.log';
        if (file_exists($appLog)) {
            $logs['app'] = [
                'name' => 'Aplikace Log',
                'file' => $appLog,
                'entries' => get_log_entries($appLog, $limit)
            ];
        }

        // Tracy log
        $tracyLog = APP_ROOT . '/var/log/tracy.log';
        if (file_exists($tracyLog)) {
            $logs['tracy'] = [
                'name' => 'Tracy Debug Log',
                'file' => $tracyLog,
                'entries' => get_log_entries($tracyLog, $limit)
            ];
        }

        return $logs;
    }
}

if (!function_exists('get_log_entries')) {
    /**
     * Získá záznamy z log souboru
     *
     * @param string $logFile
     * @param int $limit
     * @return array
     */
    function get_log_entries(string $logFile, int $limit = 100): array
    {
        if (!file_exists($logFile)) {
            return [];
        }

        $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (!$lines) {
            return [];
        }

        // Obrátit pořadí (nejnovější první)
        $lines = array_reverse($lines);

        // Omezit počet řádků
        $lines = array_slice($lines, 0, $limit);

        $entries = [];
        foreach ($lines as $line) {
            $entries[] = [
                'line' => $line,
                'timestamp' => extract_timestamp($line),
                'level' => extract_log_level($line)
            ];
        }

        return $entries;
    }
}

if (!function_exists('extract_timestamp')) {
    /**
     * Extrahuje timestamp z log řádku
     *
     * @param string $line
     * @return string|null
     */
    function extract_timestamp(string $line): ?string
    {
        // Různé formáty timestampů
        $patterns = [
            '/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]/',
            '/(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})/',
            '/(\d{2}\/\d{2}\/\d{4} \d{2}:\d{2}:\d{2})/'
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $line, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }
}

if (!function_exists('extract_log_level')) {
    /**
     * Extrahuje úroveň logu z řádku
     *
     * @param string $line
     * @return string
     */
    function extract_log_level(string $line): string
    {
        $levels = ['ERROR', 'WARNING', 'INFO', 'DEBUG', 'CRITICAL', 'ALERT', 'EMERGENCY'];

        foreach ($levels as $level) {
            if (stripos($line, $level) !== false) {
                return strtolower($level);
            }
        }

        return 'info';
    }
}

if (!function_exists('resources')) {
    /**
     * Získá cestu k resources
     *
     * @param string $path
     * @return string
     */
    function resources(string $path = ''): string
    {
        return APP_ROOT . '/resources/' . ltrim($path, '/');
    }

}

if (!function_exists('check_database_integrity')) {
    /**
     * Zkontroluje integritu databáze
     *
     * @return array
     * @noinspection D
     */
    function check_database_integrity(): array
    {
        try {
            $em = Container::get('doctrine.em');
            $connection = $em->getConnection();

            $issues = [];
            $success = true;

            // Kontrola připojení
            try {
                $connection->executeQuery('SELECT 1');
            } catch (Exception $e) {
                $issues[] = 'Nelze se připojit k databázi: ' . $e->getMessage();
                $success = false;
            }

            // Kontrola tabulek
            $tables = ['users', 'customers', 'contacts', 'deals', 'invoices'];
            foreach ($tables as $table) {
                try {
                    $result = $connection->executeQuery("SHOW TABLES LIKE '$table'");
                    if ($result->rowCount() === 0) {
                        $issues[] = "Tabulka '$table' neexistuje";
                        $success = false;
                    }
                } catch (Exception $e) {
                    $issues[] = "Chyba při kontrole tabulky '$table': " . $e->getMessage();
                    $success = false;
                }
            }

            // Kontrola integrity constraints
            try {
                $result = $connection->executeQuery("CHECK TABLE users, customers, contacts, deals, invoices");
                while ($row = $result->fetchAssociative()) {
                    if ($row['Msg_text'] !== 'OK') {
                        $issues[] = "Problém s tabulkou {$row['Table']}: {$row['Msg_text']}";
                        $success = false;
                    }
                }
            } catch (Exception $e) {
                $issues[] = 'Chyba při kontrole integrity: ' . $e->getMessage();
                $success = false;
            }

            // Kontrola foreign keys
            try {
                $result = $connection->executeQuery("
                    SELECT
                        TABLE_NAME,
                        COLUMN_NAME,
                        CONSTRAINT_NAME,
                        REFERENCED_TABLE_NAME,
                        REFERENCED_COLUMN_NAME
                    FROM information_schema.KEY_COLUMN_USAGE
                    WHERE REFERENCED_TABLE_SCHEMA = DATABASE()
                    AND REFERENCED_TABLE_NAME IS NOT NULL
                ");

                $foreignKeys = $result->fetchAllAssociative();
                foreach ($foreignKeys as $fk) {
                    // Kontrola, zda existují orphaned records
                    $checkQuery = "
                        SELECT COUNT(*) as count
                        FROM {$fk['TABLE_NAME']} t1
                        LEFT JOIN {$fk['REFERENCED_TABLE_NAME']} t2
                        ON t1.{$fk['COLUMN_NAME']} = t2.{$fk['REFERENCED_COLUMN_NAME']}
                        WHERE t2.{$fk['REFERENCED_COLUMN_NAME']} IS NULL
                        AND t1.{$fk['COLUMN_NAME']} IS NOT NULL
                    ";

                    $orphanedResult = $connection->executeQuery($checkQuery);
                    $orphanedCount = $orphanedResult->fetchAssociative()['count'];

                    if ($orphanedCount > 0) {
                        $issues[] = "Nalezeno $orphanedCount orphaned records v {$fk['TABLE_NAME']}.{$fk['COLUMN_NAME']}";
                        $success = false;
                    }
                }
            } catch (Exception $e) {
                $issues[] = 'Chyba při kontrole foreign keys: ' . $e->getMessage();
                $success = false;
            }

            $message = empty($issues) ? 'Všechny kontroly integrity proběhly úspěšně.' : implode('; ', $issues);

            return [
                'success' => $success,
                'message' => $message,
                'issues' => $issues
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Kritická chyba při kontrole integrity: ' . $e->getMessage(),
                'issues' => [$e->getMessage()]
            ];
        }
    }
}

if (!function_exists('csrf_field')) {
    /**
     * Generuje CSRF token field pro formuláře
     *
     * @return string
     * @throws RandomException
     */
    function csrf_field(): string
    {
        $token = $_SESSION['csrf_token'] ?? null;

        if (!$token) {
            $token = bin2hex(random_bytes(32));
            $_SESSION['csrf_token'] = $token;
        }

        return '<input type="hidden" name="_token" value="' . e($token) . '">';
    }
}

if (!function_exists('csrf_token')) {
    /**
     * Získá CSRF token
     *
     * @return string
     * @throws RandomException
     */
    function csrf_token(): string
    {
        $token = $_SESSION['csrf_token'] ?? null;

        if (!$token) {
            $token = bin2hex(random_bytes(32));
            $_SESSION['csrf_token'] = $token;
        }

        return $token;
    }
}

if (!function_exists('verify_csrf_token')) {
    /**
     * Ověří CSRF token
     *
     * @param string $token
     * @return bool
     */
    function verify_csrf_token(string $token): bool
    {
        $sessionToken = $_SESSION['csrf_token'] ?? null;

        if (!$sessionToken) {
            return false;
        }

        return hash_equals($sessionToken, $token);
    }
}

if (!function_exists('method_field')) {
    /**
     * Generuje hidden input pro HTTP metodu
     *
     * @param string $method
     * @return string
     */
    function method_field(string $method): string
    {
        return '<input type="hidden" name="_method" value="' . e(strtoupper($method)) . '">';
    }
}

require_once APP_ROOT . '/core/helpers/notification.php';
