<?php

namespace Core\Authorization;

use PDO;
use ReturnTypeWillChange;
use SessionHandlerInterface;

class DbSession implements SessionHandlerInterface
{
    use SessionTrait;
    private PDO $pdo;
    private string $tableName = 'sessions';
    private int $timeoutMinutes = 120; // 2 hodiny default

    public function __construct(PDO $pdo, string $tableName = 'sessions')
    {
        $this->pdo = $pdo;
        $this->tableName = $tableName;
        $this->initDatabase();
    }

    /**
     * Inicializuje databázové session
     */
    public static function initDb(PDO $pdo, string $tableName = 'sessions'): void
    {
        $dbSession = new self($pdo, $tableName);
        session_set_save_handler($dbSession, true);

        // Spustit session s databázovým handlerem
        if (session_status() !== PHP_SESSION_ACTIVE) {
            if (session_status() === PHP_SESSION_NONE) {
                ini_set('session.cookie_httponly', 1);
                ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
                ini_set('session.use_strict_mode', 1);
                ini_set('session.cookie_samesite', 'Lax');
            }
            session_start();
        }
    }

    /**
     * Vytvoří tabulku pro session, pokud neexistuje
     */
    private function initDatabase(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS `{$this->tableName}` (
            `id` varchar(255) NOT NULL,
            `data` text NOT NULL,
            `last_activity` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
            `user_id` int(11) NULL,
            `ip_address` varchar(45) NULL,
            `user_agent` text NULL,
            PRIMARY KEY (`id`),
            KEY `last_activity` (`last_activity`),
            KEY `user_id` (`user_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $this->pdo->exec($sql);
    }

    /**
     * Otevře session
     */
    public function open(string $path, string $name): bool
    {
        return true;
    }

    /**
     * Zavře session
     */
    public function close(): bool
    {
        return true;
    }

    /**
     * Přečte session data z databáze
     */
    public function read(string $id): string|false
    {
        $stmt = $this->pdo->prepare("SELECT data FROM {$this->tableName} WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? $row['data'] : '';
    }

    /**
     * Zapíše session data do databáze
     */
    public function write(string $id, string $data): bool
    {
        $userData = $this->extractUserData($data);

        $stmt = $this->pdo->prepare("
            REPLACE INTO {$this->tableName}
            (id, data, last_activity, user_id, ip_address, user_agent)
            VALUES (:id, :data, NOW(), :user_id, :ip_address, :user_agent)
        ");

        return $stmt->execute([
            'id' => $id,
            'data' => $data,
            'user_id' => $userData['user_id'] ?? null,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);
    }

    /**
     * Smaže session z databáze (pro SessionHandlerInterface)
     */
    public function destroy(string $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM {$this->tableName} WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Garbage collection - smaže staré session
     */
    #[ReturnTypeWillChange]
    public function gc(int $max_lifetime): bool
    {
        $stmt = $this->pdo->prepare("
            DELETE FROM {$this->tableName}
            WHERE last_activity < NOW() - INTERVAL :lifetime SECOND
        ");
        return $stmt->execute(['lifetime' => $max_lifetime]);
    }

    /**
     * Extrahuje user_id z session dat
     */
    private function extractUserData(string $data): array
    {
        $sessionData = [];
        $pairs = explode(';', $data);

        foreach ($pairs as $pair) {
            if (empty($pair)) continue;

            $keyValue = explode('|', $pair, 2);
            if (count($keyValue) === 2) {
                $key = $keyValue[0];
                $value = @unserialize($keyValue[1]);

                if ($value !== false && $key === 'user' && is_array($value) && isset($value['id'])) {
                    $sessionData['user_id'] = $value['id'];
                }
            }
        }

        return $sessionData;
    }

    /**
     * Získá všechny aktivní session pro uživatele
     */
    public function getUserSessions(int $userId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT id, data, last_activity, ip_address, user_agent
            FROM {$this->tableName}
            WHERE user_id = :user_id
            ORDER BY last_activity DESC
        ");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Smaže všechny session pro uživatele (kromě aktuální)
     */
    public function destroyUserSessions(int $userId, string $excludeSessionId = null): bool
    {
        $sql = "DELETE FROM {$this->tableName} WHERE user_id = :user_id";
        $params = ['user_id' => $userId];

        if ($excludeSessionId) {
            $sql .= " AND id != :exclude_id";
            $params['exclude_id'] = $excludeSessionId;
        }

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Získá statistiku session
     */
    public function getSessionStats(): array
    {
        $stats = [];

        // Celkový počet session
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM {$this->tableName}");
        $stats['total_sessions'] = $stmt->fetchColumn();

        // Počet aktivních uživatelů (unikátní user_id)
        $stmt = $this->pdo->query("SELECT COUNT(DISTINCT user_id) FROM {$this->tableName} WHERE user_id IS NOT NULL");
        $stats['active_users'] = $stmt->fetchColumn();

        // Session starší než 1 hodina
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM {$this->tableName} WHERE last_activity < NOW() - INTERVAL 1 HOUR");
        $stats['old_sessions'] = $stmt->fetchColumn();

        return $stats;
    }

    /**
     * Nastaví timeout pro session
     */
    public function setTimeout(int $minutes): void
    {
        $this->timeoutMinutes = $minutes;
    }

    /**
     * Získá timeout
     */
    public function getTimeout(): int
    {
        return $this->timeoutMinutes;
    }

    /**
     * Zkontroluje timeout session
     */
    public function checkTimeout(): void
    {
        $now = time();
        if (isset($_SESSION['_last_activity']) && ($now - $_SESSION['_last_activity']) > $this->timeoutMinutes * 60) {
            $this->clear();
        }
        $_SESSION['_last_activity'] = $now;
    }

    /**
     * Regeneruje session ID
     */
    public function regenerateId(bool $deleteOldSession = true): void
    {
        session_regenerate_id($deleteOldSession);
    }

    /**
     * Uloží session data
     */
    public function save(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }
    }


}
