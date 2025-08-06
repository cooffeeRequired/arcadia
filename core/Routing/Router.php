<?php

namespace Core\Routing;

use App\Controllers\ErrorController;
use Exception;

class Router
{
    protected array $routes = [];
    protected array $errorHandlers = [];

    public function get(string $uri, callable|array $handler): void
    {
        $this->routes['GET'][$uri] = $handler;
    }

    public function post(string $uri, callable|array $handler): void
    {
        $this->routes['POST'][$uri] = $handler;
    }

    public function put(string $uri, callable|array $handler): void
    {
        $this->routes['PUT'][$uri] = $handler;
    }

    public function delete(string $uri, callable|array $handler): void
    {
        $this->routes['DELETE'][$uri] = $handler;
    }

    /**
     * Definuje handler pro chybové stránky
     */
    public function error(int $statusCode, callable|array $handler): void
    {
        $this->errorHandlers[$statusCode] = $handler;
    }

    /**
     * Definuje 404 handler
     */
    public function notFound(callable|array $handler): void
    {
        $this->error(404, $handler);
    }

    /**
     * Definuje 500 handler
     */
    public function serverError(callable|array $handler): void
    {
        $this->error(500, $handler);
    }

    public function dispatch(string $uri): void
    {
        $uri = parse_url($uri, PHP_URL_PATH);
        $method = $_SERVER['REQUEST_METHOD'];

        // Najdi routu s parametry
        $route = $this->findRoute($method, $uri);
        
        if ($route) {
            [$handler, $params] = $route;
            
            try {
                if (is_array($handler)) {
                    [$class, $method] = $handler;
                    $controller = new $class();
                    
                    if (!empty($params)) {
                        $result = $controller->$method(...$params);
                    } else {
                        $result = $controller->$method();
                    }
                    
                    // Pokud metoda vrátila něco, vypiš to
                    if ($result !== null && $result !== '') {
                        echo $result;
                    }
                } else {
                    if (!empty($params)) {
                        $result = $handler(...$params);
                    } else {
                        $result = $handler();
                    }
                    
                    // Pokud handler vrátil něco, vypiš to
                    if ($result !== null && $result !== '') {
                        echo $result;
                    }
                }
            } catch (Exception $e) {
                $this->handleError(500, $e);
            }
        } else {
            $this->handleError(404);
        }
    }

    private function findRoute(string $method, string $uri): ?array
    {
        if (!isset($this->routes[$method])) {
            return null;
        }

        // Přímá shoda
        if (isset($this->routes[$method][$uri])) {
            return [$this->routes[$method][$uri], []];
        }

        // Hledání routy s parametry
        foreach ($this->routes[$method] as $route => $handler) {
            $pattern = $this->convertRouteToRegex($route);
            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches); // Odstraň celou shodu
                return [$handler, $matches];
            }
        }

        return null;
    }

    private function convertRouteToRegex(string $route): string
    {
        return '#^' . preg_replace('#\{([a-zA-Z]+)}#', '([^/]+)', $route) . '$#';
    }

    /**
     * Zpracuje chybovou stránku
     */
    private function handleError(int $statusCode, Exception $exception = null): void
    {
        http_response_code($statusCode);
        
        if (isset($this->errorHandlers[$statusCode])) {
            $handler = $this->errorHandlers[$statusCode];
            
            if (is_array($handler)) {
                [$class, $method] = $handler;
                $controller = new $class();
                
                if ($exception) {
                    $result = $controller->$method($exception);
                } else {
                    $result = $controller->$method();
                }
                
                if ($result !== null && $result !== '') {
                    echo $result;
                }
            } else {
                if ($exception) {
                    $result = $handler($exception);
                } else {
                    $result = $handler();
                }
                
                if ($result !== null && $result !== '') {
                    echo $result;
                }
            }
        } else {
            // Výchozí chybové stránky pomocí ErrorController
            $errorController = new ErrorController();

            echo match ($statusCode) {
                404 => $errorController->notFound(),
                500 => $errorController->serverError($exception),
                403 => $errorController->forbidden(),
                default => $errorController->error($statusCode),
            };
        }
    }
}