<?php /** @noinspection ALL */

namespace Core\Authorization;

use Predis\Client;
use ReturnTypeWillChange;
use SessionHandlerInterface;

class RedisSession implements SessionHandlerInterface
{
    use SessionTrait;

    private Client $redis;
    private string $prefix;
    private int $timeoutMinutes = 120; // 2 hodiny default
    private string $namespace;

    public function __construct(Client $redis, string $namespace = 'arcadia', string $prefix = 'session:')
    {
        $this->redis = $redis;
        $this->namespace = $namespace;
        $this->prefix = $prefix;
    }

    /**
     * Inicializuje Redis session
     */
    public static function init(Client $redis, string $namespace = 'none', string $prefix = 'ses:'): void
    {
        $redisSession = new self($redis, $namespace, $prefix);
        session_set_save_handler($redisSession, true);

        // Spustit session s Redis handlerem
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
     * Přečte session data z Redis
     */
    public function read(string $id): string|false
    {
        $key = $this->getRedisKey($id);
        debug_log("RedisSession::read() - Reading from key: " . $key);
        debug_log("RedisSession::read() - Redis client: " . get_class($this->redis));
        debug_log("RedisSession::read() - Redis connection: " . get_class($this->redis->getConnection()));

        $data = $this->redis->get($key);
        debug_log("RedisSession::read() - Raw data: " . ($data ? 'found' : 'not found'));

        if ($data === null) {
            debug_log("RedisSession::read() - No data found, returning empty string");
            return '';
        }

        // Pokud data existují, zkus je dekódovat
        $sessionData = json_decode($data, true);
        debug_log("RedisSession::read() - Decoded data: " . json_encode($sessionData));

        if ($sessionData && isset($sessionData['data'])) {
            // Aktualizuj last_activity
            debug_log("RedisSession::read() - Calling updateLastActivity()");
            $this->updateLastActivity($id);
            debug_log("RedisSession::read() - Returning session data: " . $sessionData['data']);
            return $sessionData['data'];
        } else {
            debug_log("RedisSession::read() - No valid session data found, not calling updateLastActivity()");
        }

        // Pokud data nejsou ve správném formátu, vrať prázdný string
        debug_log("RedisSession::read() - Invalid data format, returning empty string");
        return '';
    }

    /**
     * Zapíše session data do Redis
     */
    public function write(string $id, string $data): bool
    {
        $key = $this->getRedisKey($id);
        debug_log("RedisSession::write() - Writing to key: " . $key);
        debug_log("RedisSession::write() - Redis client: " . get_class($this->redis));

        // Načti existující data
        $existingData = $this->redis->get($key);
        debug_log("RedisSession::write() - Existing data: " . ($existingData ? 'found' : 'not found'));

        $userData = $this->extractUserData($data);

        // Session data - zachovej existující data pokud existují
        $sessionData = [
            'data' => $data,
            'last_activity' => time(),
            'user_id' => $userData['user_id'] ?? null,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'created_at' => time()
        ];

        // Pokud existují data, zachovej některé pole
        if ($existingData) {
            $existingSessionData = json_decode($existingData, true);
            if ($existingSessionData) {
                // Zachovej created_at pokud existuje
                if (isset($existingSessionData['created_at'])) {
                    $sessionData['created_at'] = $existingSessionData['created_at'];
                }
                // Zachovej user_id pokud existuje a nové je null
                if (isset($existingSessionData['user_id']) && $sessionData['user_id'] === null) {
                    $sessionData['user_id'] = $existingSessionData['user_id'];
                }
            }
        }

        // Ulož do Redis s TTL
        $ttl = $this->timeoutMinutes * 60;
        debug_log("RedisSession::write() - TTL: {$ttl} seconds ({$this->timeoutMinutes} minutes)");

        $result = $this->redis->setex($key, $ttl, json_encode($sessionData));
        debug_log("RedisSession::write() - Setex result: " . ($result ? 'success' : 'failed'));

                // Ověř, že data byla uložena
        $savedData = $this->redis->get($key);
        debug_log("RedisSession::write() - Verification: " . ($savedData ? 'data found' : 'data not found'));

        // Zkontroluj TTL klíče
        $keyTtl = $this->redis->ttl($key);
        debug_log("RedisSession::write() - Key TTL: {$keyTtl} seconds");

        // Zkontroluj, jestli se data skutečně uložila
        if ($savedData) {
            debug_log("RedisSession::write() - Saved data: " . substr($savedData, 0, 100) . "...");
        }

        // Zkontroluj, jestli se data skutečně uložila do Redis
        $checkData = $this->redis->get($key);
        debug_log("RedisSession::write() - Check data: " . ($checkData ? 'found' : 'not found'));
        if ($checkData) {
            debug_log("RedisSession::write() - Check data content: " . substr($checkData, 0, 100) . "...");
        }

        // Zkontroluj, jestli se data skutečně uložila do Redis po chvíli
        usleep(1000); // Počkej 1ms
        $checkData2 = $this->redis->get($key);
        debug_log("RedisSession::write() - Check data after 1ms: " . ($checkData2 ? 'found' : 'not found'));
        if ($checkData2) {
            debug_log("RedisSession::write() - Check data content after 1ms: " . substr($checkData2, 0, 100) . "...");
        }

        // Ulož metadata pro tracking
        if (isset($userData['user_id']) && $userData['user_id']) {
            $this->trackUserSession($userData['user_id'], $id);
        }

        return true;
    }

    /**
     * Smaže session z Redis
     */
    public function destroy(string $id): bool
    {
        $key = $this->getRedisKey($id);
        $this->redis->del($key);

        // Smaž z user tracking
        $this->removeUserSessionTracking($id);

        return true;
    }

    /**
     * Garbage collection - Redis to dělá automaticky pomocí TTL
     */
    #[ReturnTypeWillChange]
    public function gc(int $max_lifetime): bool
    {
        // Redis automaticky maže klíče s vypršeným TTL
        // Můžeme jen vyčistit staré tracking data
        $this->cleanupOldTrackingData();
        return true;
    }

    /**
     * Získá Redis klíč pro session
     */
    private function getRedisKey(string $id): string
    {
        $key = "{$this->namespace}:{$this->prefix}{$id}";
        debug_log("RedisSession::getRedisKey() - Generated key: " . $key . " (namespace: {$this->namespace}, prefix: {$this->prefix}, id: {$id})");
        return $key;
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

                                // Bezpečné deserializace s error handling
                $value = @unserialize($keyValue[1]);

                if ($value !== false && $key === 'user' && is_array($value) && isset($value['id'])) {
                    $sessionData['user_id'] = $value['id'];
                }
            }
        }

        return $sessionData;
    }

    /**
     * Aktualizuje last_activity pro session
     */
    private function updateLastActivity(string $id): void
    {
        $key = $this->getRedisKey($id);
        debug_log("RedisSession::updateLastActivity() - Updating key: " . $key);

        $data = $this->redis->get($key);
        debug_log("RedisSession::updateLastActivity() - Raw data: " . ($data ? 'found' : 'not found'));

        if ($data) {
            $sessionData = json_decode($data, true);
            debug_log("RedisSession::updateLastActivity() - Decoded data: " . json_encode($sessionData));

            if ($sessionData && isset($sessionData['data'])) {
                // Zachovej původní session data
                $sessionData['last_activity'] = time();
                debug_log("RedisSession::updateLastActivity() - Updating last_activity to: " . $sessionData['last_activity']);

                $ttl = $this->timeoutMinutes * 60;
                $result = $this->redis->setex($key, $ttl, json_encode($sessionData));
                debug_log("RedisSession::updateLastActivity() - Setex result: " . ($result ? 'success' : 'failed'));
            } else {
                debug_log("RedisSession::updateLastActivity() - No valid session data found");
            }
        } else {
            debug_log("RedisSession::updateLastActivity() - No data found to update");
        }
    }

    /**
     * Trackuje session pro uživatele
     */
    private function trackUserSession(int $userId, string $sessionId): void
    {
        $userKey = "{$this->namespace}:user_sessions:{$userId}";
        $this->redis->sadd($userKey, $sessionId);
        $this->redis->expire($userKey, $this->timeoutMinutes * 60);
    }

    /**
     * Odebere session z user tracking
     */
    private function removeUserSessionTracking(string $sessionId): void
    {
        // Najdi všechny user session sety a odeber session
        $pattern = "{$this->namespace}:user_sessions:*";
        $keys = $this->redis->keys($pattern);

        foreach ($keys as $key) {
            $this->redis->srem($key, $sessionId);
        }
    }

    /**
     * Vyčistí stará tracking data
     */
    private function cleanupOldTrackingData(): void
    {
        $pattern = "{$this->namespace}:user_sessions:*";
        $keys = $this->redis->keys($pattern);

        foreach ($keys as $key) {
            $sessions = $this->redis->smembers($key);
            $validSessions = [];

            foreach ($sessions as $sessionId) {
                $sessionKey = $this->getRedisKey($sessionId);
                if ($this->redis->exists($sessionKey)) {
                    $validSessions[] = $sessionId;
                }
            }

            // Aktualizuj set pouze s validními session
            $this->redis->del($key);
            if (!empty($validSessions)) {
                $this->redis->sadd($key, ...$validSessions);
                $this->redis->expire($key, $this->timeoutMinutes * 60);
            }
        }
    }

    /**
     * Získá všechny aktivní session pro uživatele
     */
    public function getUserSessions(int $userId): array
    {
        $userKey = "{$this->namespace}:user_sessions:{$userId}";
        $sessionIds = $this->redis->smembers($userKey);
        $sessions = [];

        foreach ($sessionIds as $sessionId) {
            $key = $this->getRedisKey($sessionId);
            $data = $this->redis->get($key);

            if ($data) {
                $sessionData = json_decode($data, true);
                $sessions[] = [
                    'id' => $sessionId,
                    'data' => $sessionData['data'],
                    'last_activity' => date('Y-m-d H:i:s', $sessionData['last_activity']),
                    'ip_address' => $sessionData['ip_address'],
                    'user_agent' => $sessionData['user_agent']
                ];
            }
        }

        return $sessions;
    }

    /**
     * Smaže všechny session pro uživatele (kromě aktuální)
     */
    public function destroyUserSessions(int $userId, ?string $excludeSessionId = null): bool
    {
        $userKey = "{$this->namespace}:user_sessions:{$userId}";
        $sessionIds = $this->redis->smembers($userKey);

        foreach ($sessionIds as $sessionId) {
            if ($excludeSessionId && $sessionId === $excludeSessionId) {
                continue;
            }

            $this->destroy($sessionId);
        }

        return true;
    }

    /**
     * Získá statistiku session
     */
    public function getSessionStats(): array
    {
        $stats = [];

        // Celkový počet session
        $pattern = "{$this->namespace}:{$this->prefix}*";
        $sessionKeys = $this->redis->keys($pattern);
        $stats['total_sessions'] = count($sessionKeys);

        // Počet aktivních uživatelů
        $userPattern = "{$this->namespace}:user_sessions:*";
        $userKeys = $this->redis->keys($userPattern);
        $stats['active_users'] = count($userKeys);

        // Session starší než 1 hodina
        $oldSessions = 0;
        foreach ($sessionKeys as $key) {
            $data = $this->redis->get($key);
            if ($data) {
                $sessionData = json_decode($data, true);
                if (time() - $sessionData['last_activity'] > 3600) {
                    $oldSessions++;
                }
            }
        }
        $stats['old_sessions'] = $oldSessions;

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

        /**
     * Vyčistí všechny session (pro debugging)
     */
    public function clearAllSessions(): void
    {
        $pattern = "{$this->namespace}:{$this->prefix}*";
        $keys = $this->redis->keys($pattern);
        if (!empty($keys)) {
            $this->redis->del($keys);
        }

        $userPattern = "{$this->namespace}:user_sessions:*";
        $userKeys = $this->redis->keys($userPattern);
        if (!empty($userKeys)) {
            $this->redis->del($userKeys);
        }
    }

        /**
     * Přepíše get() metodu z SessionTrait pro čtení z Redis
     */
    public function get(string $key, mixed $default = null): mixed
    {
        // Pokud $_SESSION je prázdné, zkus načíst data z Redis
        if (empty($_SESSION) && session_status() === PHP_SESSION_ACTIVE) {
            $sessionId = session_id();
            debug_log("RedisSession::get() - Session ID: " . $sessionId);

            if ($sessionId) {
                $redisKey = $this->getRedisKey($sessionId);
                debug_log("RedisSession::get() - Redis key: " . $redisKey);

                $data = $this->redis->get($redisKey);
                debug_log("RedisSession::get() - Redis data: " . ($data ? 'found' : 'not found'));

                if ($data) {
                    $sessionData = json_decode($data, true);
                    debug_log("RedisSession::get() - Decoded data: " . json_encode($sessionData));

                    if ($sessionData && isset($sessionData['data'])) {
                        // Dekóduj session data do $_SESSION
                        $decoded = session_decode($sessionData['data']);
                        debug_log("RedisSession::get() - Session decode result: " . ($decoded ? 'success' : 'failed'));
                        debug_log("RedisSession::get() - $_SESSION after decode: " . json_encode($_SESSION));
                    }
                }
            }
        }

        $result = $_SESSION[$key] ?? $default;
        debug_log("RedisSession::get('{$key}') returning: " . json_encode($result));
        return $result;
    }

    /**
     * Vyčistí všechny session při inicializaci (pro kompatibilitu)
     */
    public static function clearOldSessions(Client $redis, string $namespace = 'arcadia'): void
    {
        $pattern = "{$namespace}:session:*";
        $keys = $redis->keys($pattern);
        if (!empty($keys)) {
            $redis->del($keys);
        }

        $userPattern = "{$namespace}:user_sessions:*";
        $userKeys = $redis->keys($userPattern);
        if (!empty($userKeys)) {
            $redis->del($userKeys);
        }
    }
}
