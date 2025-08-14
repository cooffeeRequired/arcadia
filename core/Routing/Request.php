<?php

namespace Core\Routing;

use Core\Authorization\Session;

/**
 * Třída pro zpracování HTTP požadavků
 */
class Request
{
    private static ?Request $instance = null;
    private Session|null $session = null;
    private ?string $uri = null;
    private ?string $method = null;
    private mixed $query;
    private mixed $post;
    private array $server;

    private function __construct()
    {
        $this->uri = $_SERVER['REQUEST_URI'] ?? '/';
        $this->method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $this->query = $_GET;
        $this->post = $_POST;
        $this->server = $_SERVER;
        // Session se vytvoří lazy loading
    }

    /**
     * Získá instanci Request (Singleton pattern)
     *
     * @return self
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Získá session
     *
     * @return Session
     */
    public function getSession(): Session
    {
        if ($this->session === null) {
            // Použij session z containeru pokud je dostupná
            $session = \Core\Facades\Container::get('session');
            if ($session) {
                $this->session = $session;
            } else {
                $this->session = new Session();
            }
        }
        return $this->session;
    }

    /**
     * Získá URI
     *
     * @return string|null
     */
    public function getUri(): ?string
    {
        return $this->uri;
    }

    /**
     * Získá HTTP metodu
     *
     * @return string|null
     */
    public function getMethod(): ?string
    {
        return $this->method;
    }

    /**
     * Zkontroluje, zda aktuální URI odpovídá danému patternu
     *
     * @param string $pattern
     * @return bool
     */
    public function is(string $pattern): bool
    {
        $normalizePath = function (string $path): string {
            $path = urldecode($path);
            $path = preg_replace('#/+#', '/', $path); // odstraní vícenásobné lomítka
            $path = rtrim($path, '/'); // odstraní trailing slash
            return $path === '' ? '/' : $path;
        };

        $currentPath = $normalizePath(parse_url($this->uri, PHP_URL_PATH) ?? '/');
        $normalizedPattern = $normalizePath(rtrim($pattern, '*'));

        if (str_ends_with($pattern, '*')) {
            return str_starts_with($currentPath, $normalizedPattern);
        }
        return $currentPath === $normalizedPattern;
    }

    /**
     * Získá GET parametr
     *
     * @param string $key
     * @param mixed|null $default
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }

    /**
     * Získá POST parametr
     *
     * @param string $key
     * @param mixed|null $default
     * @return mixed
     */
    public function post(string $key, mixed $default = null): mixed
    {
        return $this->post[$key] ?? $default;
    }

    /**
     * Získá header
     *
     * @param string $key
     * @param string|null $default
     * @return string|null
     */
    public function header(string $key, ?string $default = null): ?string
    {
        // Speciální případy pro některé hlavičky
        $specialHeaders = [
            'CONTENT_TYPE' => 'CONTENT_TYPE',
            'CONTENT_LENGTH' => 'CONTENT_LENGTH',
            'CONTENT_MD5' => 'CONTENT_MD5',
            'AUTHORIZATION' => 'HTTP_AUTHORIZATION',
            'PHP_AUTH_USER' => 'PHP_AUTH_USER',
            'PHP_AUTH_PW' => 'PHP_AUTH_PW',
            'AUTH_TYPE' => 'AUTH_TYPE'
        ];

        $normalizedKey = strtoupper(str_replace('-', '_', $key));

        if (isset($specialHeaders[$normalizedKey])) {
            $headerKey = $specialHeaders[$normalizedKey];
        } else {
            $headerKey = 'HTTP_' . $normalizedKey;
        }

        return $this->server[$headerKey] ?? $default;
    }

    /**
     * Zkontroluje, zda je požadavek AJAX
     *
     * @return bool
     */
    public function isAjax(): bool
    {
        return $this->header('X-Requested-With') === 'XMLHttpRequest';
    }

    /**
     * Zkontroluje, zda je požadavek POST
     *
     * @return bool
     */
    public function isPost(): bool
    {
        return $this->method === 'POST';
    }

    /**
     * Zkontroluje, zda je požadavek GET
     *
     * @return bool
     */
    public function isGet(): bool
    {
        return $this->method === 'GET';
    }

    /**
     * Získá všechna externí session data
     *
     * @param bool $asArray Pokud true, vrátí jako array, jinak jako object
     * @return array|object
     */
    public function getExternalSession(bool $asArray = false): array|object
    {
        $session = $this->getSession();
        $sessionId = session_id();

        $externalData = [];

        if ($sessionId) {
            // Načti data z Redis
            if ($session->redis) {
                $pattern = "arcadia:session:*";
                $keys = $session->redis->keys($pattern);

                foreach ($keys as $key) {
                    $data = $session->redis->get($key);
                    if ($data) {
                        $sessionData = json_decode($data, true);
                        if ($sessionData && isset($sessionData['data'])) {
                            $sessionIdFromKey = str_replace("arcadia:session:", "", $key);
                            $decodedData = unserialize(base64_decode($sessionData['data']));

                            $externalData[$sessionIdFromKey] = [
                                'data' => $decodedData,
                                'last_activity' => date('Y-m-d H:i:s', $sessionData['last_activity']),
                                'user_id' => $sessionData['user_id'],
                                'ip_address' => $sessionData['ip_address'],
                                'user_agent' => $sessionData['user_agent'],
                                'created_at' => date('Y-m-d H:i:s', $sessionData['created_at'])
                            ];
                        }
                    }
                }
            }

            // Načti data z databáze
            if ($session->pdo) {
                $stmt = $session->pdo->prepare("SELECT * FROM sessions ORDER BY last_activity DESC");
                $stmt->execute();
                $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

                foreach ($rows as $row) {
                    $decodedData = unserialize(base64_decode($row['data']));
                    $externalData['db_' . $row['id']] = [
                        'data' => $decodedData,
                        'last_activity' => $row['last_activity'],
                        'user_id' => $row['user_id'],
                        'ip_address' => $row['ip_address'],
                        'user_agent' => $row['user_agent'],
                        'created_at' => $row['created_at'] ?? null
                    ];
                }
            }
        }

        return $asArray ? $externalData[$sessionId]['data'] : (object) $externalData[$sessionId]['data'];
    }

    /**
     * Získá konkrétní hodnotu z externí session podle klíče
     *
     * @param string $key Klíč ve formátu "session_id.key.subkey" nebo "admins.test"
     * @return mixed
     */
    public function getExternalSessionValue(string $key): mixed
    {
        $session = $this->getSession();
        $parts = explode('.', $key);

        if (count($parts) < 2) {
            return null;
        }

        $sessionId = $parts[0];
        $dataKey = implode('.', array_slice($parts, 1));

        // Zkus načíst z Redis
        if ($session->redis) {
            $redisKey = "arcadia:session:{$sessionId}";
            $data = $session->redis->get($redisKey);

            if ($data) {
                $sessionData = json_decode($data, true);
                if ($sessionData && isset($sessionData['data'])) {
                    $decodedData = unserialize(base64_decode($sessionData['data']));
                    return $this->getNestedValue($decodedData, $dataKey);
                }
            }
        }

        // Zkus načíst z databáze
        if ($session->pdo) {
            $stmt = $session->pdo->prepare("SELECT data FROM sessions WHERE id = :id LIMIT 1");
            $stmt->execute(['id' => $sessionId]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($row) {
                $decodedData = unserialize(base64_decode($row['data']));
                return $this->getNestedValue($decodedData, $dataKey);
            }
        }

        return null;
    }

    /**
     * Nastaví hodnotu do externí session
     *
     * @param string $key Klíč ve formátu "session_id.key.subkey"
     * @param mixed $value Hodnota k uložení
     * @param int $ttl TTL v sekundách (default: 3600)
     * @return bool
     */
    public function setExternalSession(string $key, mixed $value, int $ttl = 3600): bool
    {
        $key = session_id() . '.' . $key;
        $session = $this->getSession();
        $parts = explode('.', $key);

        if (count($parts) < 2) {
            return false;
        }

        $sessionId = $parts[0];
        $dataKey = implode('.', array_slice($parts, 1));

        // Načti existující data
        $existingData = [];

        // Zkus načíst z Redis
        if ($session->redis) {
            $redisKey = "arcadia:session:{$sessionId}";
            $data = $session->redis->get($redisKey);

            if ($data) {
                $sessionData = json_decode($data, true);
                if ($sessionData && isset($sessionData['data'])) {
                    $existingData = unserialize(base64_decode($sessionData['data']));
                }
            }
        }

        // Nastav novou hodnotu
        $this->setNestedValue($existingData, $dataKey, $value);

        // Ulož zpět do Redis
        if ($session->redis) {
            $serializedData = base64_encode(serialize($existingData));

            $sessionData = [
                'data' => $serializedData,
                'last_activity' => time(),
                'user_id' => $this->extractUserId($existingData),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
                'created_at' => time()
            ];

            $redisKey = "arcadia:session:{$sessionId}";
            $result = $session->redis->setex($redisKey, $ttl, json_encode($sessionData));
            return $result->getPayload() === 'OK';
        }

        return false;
    }

    /**
     * Získá vnořenou hodnotu z pole podle tečkové notace
     *
     * @param array $array
     * @param string $key
     * @return mixed
     */
    private function getNestedValue(array $array, string $key): mixed
    {
        $keys = explode('.', $key);
        $current = $array;

        foreach ($keys as $k) {
            if (!is_array($current) || !array_key_exists($k, $current)) {
                return null;
            }
            $current = $current[$k];
        }

        return $current;
    }

    /**
     * Nastaví vnořenou hodnotu do pole podle tečkové notace
     *
     * @param array $array
     * @param string $key
     * @param mixed $value
     * @return void
     */
    private function setNestedValue(array &$array, string $key, mixed $value): void
    {
        $keys = explode('.', $key);
        $current = &$array;

        foreach ($keys as $k) {
            if (!isset($current[$k]) || !is_array($current[$k])) {
                $current[$k] = [];
            }
            $current = &$current[$k];
        }

        $current = $value;
    }

    /**
     * Extrahuje user_id z session dat
     *
     * @param array $sessionData
     * @return int|null
     */
    private function extractUserId(array $sessionData): ?int
    {
        if (isset($sessionData['user']['id'])) {
            return (int) $sessionData['user']['id'];
        }
        return null;
    }
}
