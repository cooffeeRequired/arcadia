<?php

namespace Core\Routing;

use Core\Authorization\Session;
use Symfony\Component\VarDumper\VarDumper;

/**
 * Třída pro zpracování HTTP požadavků
 */
class Request
{
    private static ?Request $instance = null;
    private ?Session $session = null;
    private ?string $uri;
    private ?string $method;
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
        $this->session = new Session();
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
     * Získá aktuální URI
     * 
     * @return string
     */
    public function getUri(): string
    {
        return $this->uri;
    }

    /**
     * Získá HTTP metodu
     * 
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
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
     * @param mixed $default
     * @return mixed
     */
    public function header(string $key, string $default = null): mixed
    {
        $headerKey = 'HTTP_' . strtoupper(str_replace('-', '_', $key));
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

    public function getSession(): ?Session
    {
        return $this->session;
    }


} 