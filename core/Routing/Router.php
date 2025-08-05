<?php

namespace Core\Routing;

class Router
{
    protected array $routes = [];

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

    public function dispatch(string $uri): void
    {
        $uri = parse_url($uri, PHP_URL_PATH);
        $method = $_SERVER['REQUEST_METHOD'];

        // Najdi routu s parametry
        $route = $this->findRoute($method, $uri);
        
        if ($route) {
            [$handler, $params] = $route;
            
            if (is_array($handler)) {
                [$class, $method] = $handler;
                $controller = new $class();
                
                if (!empty($params)) {
                    echo $controller->$method(...$params);
                } else {
                    echo $controller->$method();
                }
            } else {
                if (!empty($params)) {
                    echo $handler(...$params);
                } else {
                    echo $handler();
                }
            }
        } else {
            http_response_code(404);
            echo "404 Not Found";
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
}