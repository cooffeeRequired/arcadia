<?php

namespace Core\Http;

use Core\Authorization\Session;
use Core\Facades\Container;

class Request
{
    private static ?Request $instance = null;
    private Session|null $session = null;
    private ?string $uri = null;
    private ?string $method = null;
    private array $query;
    private array $post;
    private array $server;
    private array $files;
    private array $cookies;

    private function __construct()
    {
        $this->uri = $_SERVER['REQUEST_URI'] ?? '/';
        $this->method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $this->query = $_GET;
        $this->post = $_POST;
        $this->server = $_SERVER;
        $this->files = $_FILES;
        $this->cookies = $_COOKIE;
    }

    /**
     * Získá instanci Request (Singleton pattern)
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
     */
    public function getSession(): Session
    {
        if ($this->session === null) {
            $session = Container::get('session');
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
     */
    public function getUri(): ?string
    {
        return $this->uri;
    }

    /**
     * Získá HTTP metodu
     */
    public function getMethod(): ?string
    {
        return $this->method;
    }

    /**
     * Získá query parametr
     */
    public function query(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }

    /**
     * Získá POST parametr
     */
    public function input(string $key, mixed $default = null): mixed
    {
        return $this->post[$key] ?? $default;
    }

    /**
     * Získá cookie
     */
    public function cookie(string $key, mixed $default = null): mixed
    {
        return $this->cookies[$key] ?? $default;
    }

    /**
     * Získá soubor
     */
    public function file(string $key): ?array
    {
        return $this->files[$key] ?? null;
    }

    /**
     * Získá všechny query parametry
     */
    public function allQuery(): array
    {
        return $this->query;
    }

    /**
     * Získá všechny POST parametry
     */
    public function allInput(): array
    {
        return $this->post;
    }

    /**
     * Získá všechny parametry (query + post)
     */
    public function all(): array
    {
        return array_merge($this->query, $this->post);
    }

    /**
     * Zkontroluje, zda parametr existuje
     */
    public function has(string $key): bool
    {
        return isset($this->query[$key]) || isset($this->post[$key]);
    }

    /**
     * Zkontroluje, zda je požadavek AJAX
     */
    public function isAjax(): bool
    {
        return $this->header('X-Requested-With') === 'XMLHttpRequest';
    }

    /**
     * Zkontroluje, zda je požadavek POST
     */
    public function isPost(): bool
    {
        return $this->method === 'POST';
    }

    /**
     * Zkontroluje, zda je požadavek GET
     */
    public function isGet(): bool
    {
        return $this->method === 'GET';
    }

    /**
     * Zkontroluje, zda je požadavek PUT
     */
    public function isPut(): bool
    {
        return $this->method === 'PUT';
    }

    /**
     * Zkontroluje, zda je požadavek DELETE
     */
    public function isDelete(): bool
    {
        return $this->method === 'DELETE';
    }

    /**
     * Zkontroluje, zda je požadavek PATCH
     */
    public function isPatch(): bool
    {
        return $this->method === 'PATCH';
    }

    /**
     * Zkontroluje, zda je požadavek HEAD
     */
    public function isHead(): bool
    {
        return $this->method === 'HEAD';
    }

    /**
     * Zkontroluje, zda je požadavek OPTIONS
     */
    public function isOptions(): bool
    {
        return $this->method === 'OPTIONS';
    }

    /**
     * Zkontroluje, zda je požadavek secure (HTTPS)
     */
    public function isSecure(): bool
    {
        return isset($this->server['HTTPS']) && $this->server['HTTPS'] !== 'off';
    }

    /**
     * Získá IP adresu klienta
     */
    public function getClientIp(): ?string
    {
        return $this->server['HTTP_X_FORWARDED_FOR'] 
            ?? $this->server['HTTP_CLIENT_IP'] 
            ?? $this->server['REMOTE_ADDR'] 
            ?? null;
    }

    /**
     * Získá User Agent
     */
    public function getUserAgent(): ?string
    {
        return $this->server['HTTP_USER_AGENT'] ?? null;
    }

    /**
     * Získá referer
     */
    public function getReferer(): ?string
    {
        return $this->server['HTTP_REFERER'] ?? null;
    }

    /**
     * Získá HTTP header
     */
    public function header(string $key, ?string $default = null): ?string
    {
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
     * Získá všechny hlavičky
     */
    public function getHeaders(): array
    {
        $headers = [];
        foreach ($this->server as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $headerName = str_replace('_', '-', strtolower(substr($key, 5)));
                $headers[$headerName] = $value;
            }
        }
        return $headers;
    }

    /**
     * Zkontroluje, zda hlavička existuje
     */
    public function hasHeader(string $key): bool
    {
        return $this->header($key) !== null;
    }

    /**
     * Získá content type
     */
    public function getContentType(): ?string
    {
        return $this->header('Content-Type');
    }

    /**
     * Získá content length
     */
    public function getContentLength(): ?int
    {
        $length = $this->header('Content-Length');
        return $length ? (int)$length : null;
    }

    /**
     * Získá raw body
     */
    public function getBody(): string
    {
        return file_get_contents('php://input') ?: '';
    }

    /**
     * Získá JSON body
     */
    public function getJson(): ?array
    {
        $body = $this->getBody();
        if (empty($body)) {
            return null;
        }

        $json = json_decode($body, true);
        return json_last_error() === JSON_ERROR_NONE ? $json : null;
    }

    /**
     * Zkontroluje, zda je požadavek JSON
     */
    public function isJson(): bool
    {
        $contentType = $this->getContentType();
        return $contentType && str_contains($contentType, 'application/json');
    }

    /**
     * Zkontroluje, zda je požadavek multipart/form-data
     */
    public function isMultipart(): bool
    {
        $contentType = $this->getContentType();
        return $contentType && str_contains($contentType, 'multipart/form-data');
    }

    /**
     * Zkontroluje, zda je požadavek application/x-www-form-urlencoded
     */
    public function isForm(): bool
    {
        $contentType = $this->getContentType();
        return $contentType && str_contains($contentType, 'application/x-www-form-urlencoded');
    }

    /**
     * Získá všechny soubory
     */
    public function getFiles(): array
    {
        return $this->files;
    }

    /**
     * Zkontroluje, zda má požadavek soubory
     */
    public function hasFiles(): bool
    {
        return !empty($this->files);
    }

    /**
     * Získá všechny cookies
     */
    public function getCookies(): array
    {
        return $this->cookies;
    }

    /**
     * Zkontroluje, zda cookie existuje
     */
    public function hasCookie(string $key): bool
    {
        return isset($this->cookies[$key]);
    }

    /**
     * Získá všechny server proměnné
     */
    public function getServer(): array
    {
        return $this->server;
    }

    /**
     * Získá server proměnnou
     */
    public function server(string $key, mixed $default = null): mixed
    {
        return $this->server[$key] ?? $default;
    }

    /**
     * Zkontroluje, zda server proměnná existuje
     */
    public function hasServer(string $key): bool
    {
        return isset($this->server[$key]);
    }

    /**
     * Získá všechny externí session data
     */
    public function getExternalSession(bool $asArray = false): array|object
    {
        $session = $this->getSession();
        $data = $session->all();
        
        if ($asArray) {
            return $data;
        }
        
        return (object)$data;
    }

    /**
     * Zkontroluje, zda aktuální URI odpovídá danému patternu
     */
    public function is(string $pattern): bool
    {
        $normalizePath = function (string $path): string {
            $path = trim($path, '/');
            return $path === '' ? '/' : '/' . $path;
        };

        $currentPath = $normalizePath($this->uri);
        $patternPath = $normalizePath($pattern);

        return $currentPath === $patternPath;
    }

    /**
     * Zkontroluje, zda URI začíná daným prefixem
     */
    public function startsWith(string $prefix): bool
    {
        $normalizePath = function (string $path): string {
            $path = trim($path, '/');
            return $path === '' ? '/' : '/' . $path;
        };

        $currentPath = $normalizePath($this->uri);
        $prefixPath = $normalizePath($prefix);

        return str_starts_with($currentPath, $prefixPath);
    }

    /**
     * Zkontroluje, zda URI končí daným suffixem
     */
    public function endsWith(string $suffix): bool
    {
        $normalizePath = function (string $path): string {
            $path = trim($path, '/');
            return $path === '' ? '/' : '/' . $path;
        };

        $currentPath = $normalizePath($this->uri);
        $suffixPath = $normalizePath($suffix);

        return str_ends_with($currentPath, $suffixPath);
    }
}
