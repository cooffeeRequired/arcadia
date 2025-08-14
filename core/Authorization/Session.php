<?php

namespace Core\Authorization;

use AllowDynamicProperties;
use Predis\Client;
use PDO;

#[AllowDynamicProperties]
class Session
{
    use SessionTrait;

    public private(set) ?Client $redis {
        get {
            return $this->redis;
        }
    }
    public private(set) ?PDO $pdo {
        get {
            return $this->pdo;
        }
    }
    private string $namespace = 'arcadia';
    private int $timeoutMinutes = 120;

    public function __construct(?Client $redis = null, ?PDO $pdo = null, string $namespace = 'arcadia')
    {
        $this->redis = $redis;
        $this->pdo = $pdo;
        $this->namespace = $namespace;
    }

    public static function init(): void
    {
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
     * Uloží session data do Redis (base64 serializace)
     */
    public function saveToRedis(string $sessionId, array $sessionData): bool
    {
        if (!$this->redis) {
            return false;
        }

        $key = "{$this->namespace}:session:{$sessionId}";
        $serializedData = base64_encode(serialize($sessionData));

        $data = [
            'data' => $serializedData,
            'last_activity' => time(),
            'user_id' => $this->extractUserId($sessionData),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'created_at' => time()
        ];

        $ttl = $this->timeoutMinutes * 60;
        $result = $this->redis->setex($key, $ttl, json_encode($data));
        return $result->getPayload() === 'OK';
    }

    /**
     * Načte session data z Redis (base64 deserializace)
     */
    public function loadFromRedis(string $sessionId): ?array
    {
        if (!$this->redis) {
            return null;
        }

        $key = "{$this->namespace}:session:{$sessionId}";
        $data = $this->redis->get($key);

        if (!$data) {
            return null;
        }

        $sessionData = json_decode($data, true);
        if (!$sessionData || !isset($sessionData['data'])) {
            return null;
        }

        $unserializedData = unserialize(base64_decode($sessionData['data']));
        return is_array($unserializedData) ? $unserializedData : null;
    }

    /**
     * Uloží session data do databáze (base64 serializace)
     */
    public function saveToDatabase(string $sessionId, array $sessionData): bool
    {
        if (!$this->pdo) {
            return false;
        }

        $serializedData = base64_encode(serialize($sessionData));
        $userId = $this->extractUserId($sessionData);

        $stmt = $this->pdo->prepare("
            REPLACE INTO sessions
            (id, data, last_activity, user_id, ip_address, user_agent)
            VALUES (:id, :data, NOW(), :user_id, :ip_address, :user_agent)
        ");

        return $stmt->execute([
            'id' => $sessionId,
            'data' => $serializedData,
            'user_id' => $userId,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);
    }

    /**
     * Načte session data z databáze (base64 deserializace)
     */
    public function loadFromDatabase(string $sessionId): ?array
    {
        if (!$this->pdo) {
            return null;
        }

        $stmt = $this->pdo->prepare("SELECT data FROM sessions WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $sessionId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        $unserializedData = unserialize(base64_decode($row['data']));
        return is_array($unserializedData) ? $unserializedData : null;
    }

    /**
     * Uloží aktuální session data (podle dostupnosti Redis/databáze)
     */
    public function saveCurrentSession(): bool
    {
        $sessionId = session_id();
        if (!$sessionId || empty($_SESSION)) {
            return false;
        }

        // Zkus Redis první
        if ($this->redis) {
            return $this->saveToRedis($sessionId, $_SESSION);
        }

        // Jinak databáze
        if ($this->pdo) {
            return $this->saveToDatabase($sessionId, $_SESSION);
        }

        return false;
    }

    /**
     * Načte session data (podle dostupnosti Redis/databáze)
     */
    public function loadSessionData(): bool
    {
        $sessionId = session_id();
        if (!$sessionId) {
            return false;
        }

        // Zkus Redis první
        if ($this->redis) {
            $data = $this->loadFromRedis($sessionId);
            if ($data) {
                $_SESSION = $data;
                return true;
            }
        }

        // Jinak databáze
        if ($this->pdo) {
            $data = $this->loadFromDatabase($sessionId);
            if ($data) {
                $_SESSION = $data;
                return true;
            }
        }

        return false;
    }

    /**
     * Extrahuje user_id z session dat
     */
    private function extractUserId(array $sessionData): ?int
    {
        if (isset($sessionData['user']['id'])) {
            return (int) $sessionData['user']['id'];
        }
        return null;
    }

    /**
     * Získá aktivitu uživatele
     */
    public function getUserActivity(int $userId): array
    {
        $activity = [];

        // Z Redis
        if ($this->redis) {
            $pattern = "{$this->namespace}:session:*";
            $keys = $this->redis->keys($pattern);

            foreach ($keys as $key) {
                $data = $this->redis->get($key);
                if ($data) {
                    $sessionData = json_decode($data, true);
                    if ($sessionData && $sessionData['user_id'] == $userId) {
                        $activity[] = [
                            'session_id' => str_replace("{$this->namespace}:session:", '', $key),
                            'last_activity' => date('Y-m-d H:i:s', $sessionData['last_activity']),
                            'ip_address' => $sessionData['ip_address'],
                            'user_agent' => $sessionData['user_agent'],
                            'source' => 'redis'
                        ];
                    }
                }
            }
        }

        // Z databáze
        if ($this->pdo) {
            $stmt = $this->pdo->prepare("
                SELECT id, last_activity, ip_address, user_agent
                FROM sessions
                WHERE user_id = :user_id
                ORDER BY last_activity DESC
            ");
            $stmt->execute(['user_id' => $userId]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($rows as $row) {
                $activity[] = [
                    'session_id' => $row['id'],
                    'last_activity' => $row['last_activity'],
                    'ip_address' => $row['ip_address'],
                    'user_agent' => $row['user_agent'],
                    'source' => 'database'
                ];
            }
        }

        return $activity;
    }

    /**
     * Smaže session data
     */
    public function deleteSession(string $sessionId): bool
    {
        $success = true;

        // Smaž z Redis
        if ($this->redis) {
            $key = "{$this->namespace}:session:{$sessionId}";
            $this->redis->del($key);
        }

        // Smaž z databáze
        if ($this->pdo) {
            $stmt = $this->pdo->prepare("DELETE FROM sessions WHERE id = :id");
            $success = $stmt->execute(['id' => $sessionId]);
        }

        return $success;
    }

    /**
     * Nastaví timeout
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
}
