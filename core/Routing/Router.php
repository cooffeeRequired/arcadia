<?php /** @noinspection ALL */

namespace Core\Routing;

use Core\Http\Request;
use Core\Http\Response\AbstractResponse;
use Core\Http\Response\ViewResponse;
use Core\Logging\RenderLogger;
use Core\Events\EventBus;
use Exception;

class Router
{
    public private(set) array $routes = [];
    private array $routeMap = []; // Hash mapa pro rychlé vyhledávání
    private array $parameterizedRoutes = []; // Routes s parametry
    private array $namedRoutes = []; // Pojmenované routes
    private array $middleware = [];
    private array $groupStack = [];

    /**
     * Přidá GET route
     */
    public function get(string $uri, callable|array $action): Route
    {
        return $this->addRoute(['GET'], $uri, $action);
    }

    /**
     * Přidá POST route
     */
    public function post(string $uri, callable|array $action): Route
    {
        return $this->addRoute(['POST'], $uri, $action);
    }

    /**
     * Přidá PUT route
     */
    public function put(string $uri, callable|array $action): Route
    {
        return $this->addRoute(['PUT'], $uri, $action);
    }

    /**
     * Přidá DELETE route
     */
    public function delete(string $uri, callable|array $action): Route
    {
        return $this->addRoute(['DELETE'], $uri, $action);
    }

    /**
     * Přidá PATCH route
     */
    public function patch(string $uri, callable|array $action): Route
    {
        return $this->addRoute(['PATCH'], $uri, $action);
    }

    /**
     * Přidá route pro všechny metody
     */
    public function any(string $uri, callable|array $action): Route
    {
        return $this->addRoute(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], $uri, $action);
    }

    /**
     * Přidá route
     */
    private function addRoute(array $methods, string $uri, callable|array $action): Route
    {
        // Aplikuj prefix ze skupin
        $fullUri = $this->buildUri($uri);
        $route = new Route($methods, $fullUri, $action);

        // Aplikuj middleware ze skupin
        if (!empty($this->groupStack)) {
            $currentGroup = $this->groupStack[count($this->groupStack) - 1];
            if (isset($currentGroup['middleware']) && !empty($currentGroup['middleware'])) {
                $route->middleware($currentGroup['middleware']);
            }
        }

        $this->routes[] = $route;

        // Přidej do hash mapy pro rychlé vyhledávání
        foreach ($methods as $method) {
            $key = $method . ':' . $fullUri;
            $this->routeMap[$key] = $route;
        }

        // Pokud route obsahuje parametry, přidej do separátního seznamu
        if (strpos($fullUri, '{') !== false) {
            foreach ($methods as $method) {
                $this->parameterizedRoutes[$method][] = $route;
            }
        }

        // Pokud route má název, přidej do pojmenovaných routes
        if ($route->getName()) {
            $this->namedRoutes[$route->getName()] = $route;
        }

        return $route;
    }

    /**
     * Sestaví URI s prefixy ze skupin
     */
    private function buildUri(string $uri): string
    {
        $fullUri = $uri;

        foreach ($this->groupStack as $index => $group) {
            if (isset($group['prefix'])) {
                $fullUri = $group['prefix'] . '/' . ltrim($fullUri, '/');
            }
        }
        return $fullUri;
    }

    /**
     * Vytvoří skupinu routes
     */
    public function group(array $attributes, callable $callback): void
    {
        // Inicializuj middleware pole pokud není nastaveno
        if (!isset($attributes['middleware'])) {
            $attributes['middleware'] = [];
        }

        // Inicializuj prefix pokud není nastaven
        if (!isset($attributes['prefix'])) {
            $attributes['prefix'] = '';
        }

        $this->groupStack[] = $attributes;

        $callback($this);

        array_pop($this->groupStack);
    }

    /**
     * Přidá middleware
     */
    public function middleware(string $middleware): self
    {
        if (!empty($this->groupStack)) {
            $currentGroup = &$this->groupStack[count($this->groupStack) - 1];
            if (!isset($currentGroup['middleware'])) {
                $currentGroup['middleware'] = [];
            }
            $currentGroup['middleware'][] = $middleware;
        }
        return $this;
    }

    /**
     * Zpracuje požadavek
     */
    public function dispatch(Request $request): void
    {
        $startTime = microtime(true);

        try {
            $route = $this->findRoute($request);

            if (!$route) {
                $this->handleNotFound($request);
                return;
            }
            $this->applyMiddleware($route, $request);
            $result = $this->executeRoute($route, $request);
            RenderLogger::logRender('route', $startTime, $result);
            if ($result instanceof AbstractResponse) {
                $result->send();
            } else {
                echo $result;
            }

        } catch (Exception $e) {
            debug_log("❌ Router Exception: " . $e->getMessage());
            $this->handleException($e, $request);
        }
    }

    /**
     * Najde odpovídající route
     */
    private function findRoute(Request $request): ?Route
    {
        $time = microtime(true);
        $method = $request->getMethod();
        $uri = $request->getUri();

        // Nejdříve zkus přímou shodu v hash mapě
        $directKey = $method . ':' . $uri;


        if (isset($this->routeMap[$directKey])) {
            debug_log("✅ Route found: $method $uri");
            return $this->routeMap[$directKey];
        }

        if (isset($this->routeMap[$directKey . '/'])) {
            debug_log("✅ Route found: $method $uri");
            return $this->routeMap[$directKey . '/'];
        }

        // Pokud není přímá shoda, hledej routes s parametry
        if (isset($this->parameterizedRoutes[$method])) {
            foreach ($this->parameterizedRoutes[$method] as $route) {
                if ($route->matches($uri)) {
                    debug_log("✅ Route found: $method " . $route->getUri());
                    return $route;
                }
            }
        }

        return null;
    }

    /**
     * Aplikuje middleware
     */
    private function applyMiddleware(Route $route, Request $request): void
    {
        $middleware = $route->getMiddleware();

        foreach ($middleware as $middlewareName) {
            if (method_exists(Middleware::class, $middlewareName)) {
                Middleware::$middlewareName($request);
            }
        }
    }

    /**
     * Spustí route
     */
    private function executeRoute(Route $route, Request $request): AbstractResponse
    {
        $action = $route->getAction();

        if (is_callable($action)) {
            return $action($request);
        }

        if (is_array($action)) {
            [$controller, $method] = $action;

            $controllerInstance = new $controller();

            // Předaj request do controlleru
            if (method_exists($controllerInstance, 'setRequest')) {
                $controllerInstance->setRequest($request);
            }

            // Předaj parametry z route podle názvu
            $parameters = $route->getParameters();
            if (!empty($parameters)) {
                // Zkus najít metodu s parametry podle názvu
                $reflection = new \ReflectionMethod($controllerInstance, $method);
                $methodParams = $reflection->getParameters();

                $args = [];
                foreach ($methodParams as $param) {
                    $paramName = $param->getName();
                    if (isset($parameters[$paramName])) {
                        $args[] = $parameters[$paramName];
                    } elseif ($param->isDefaultValueAvailable()) {
                        $args[] = $param->getDefaultValue();
                    } else {
                        $args[] = null;
                    }
                }

                return $controllerInstance->$method(...$args);
            }

            return $controllerInstance->$method();
        }

        throw new Exception('Invalid route action');
    }

        /**
     * Zpracuje 404 chybu
     */
    private function handleNotFound(Request $request): void
    {
        debug_log("❌ Route not found: " . $request->getMethod() . " " . $request->getUri());

        $errorController = new \App\Controllers\ErrorController();
        $response = $errorController->showNotFound();

        if ($response instanceof AbstractResponse) {
            $response->send();
        } else {
            http_response_code(404);
            echo '404 - Stránka nenalezena';
        }
    }

    /**
     * Zpracuje výjimku
     */
    private function handleException(Exception $e, Request $request): void
    {
        if (getenv('APP_ENV') === 'development') {
            \Tracy\Debugger::log($e, \Tracy\ILogger::EXCEPTION);

            $html = \Tracy\Debugger::getBlueScreen()->render($e);

            if ($html) {
                http_response_code(500);
                echo $html;
                exit;
            }
        }
        $errorController = new ErrorController();
        $response = $errorController->showServerError($e);

        if ($response instanceof AbstractResponse) {
            $response->send();
        } else {
            http_response_code(500);
            echo '500 - Interní chyba serveru';
        }
    }

    /**
     * Získá všechny routes
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    /**
     * Získá route podle názvu
     */
    public function getRouteByName(string $name): ?Route
    {
        return $this->namedRoutes[$name] ?? null;
    }

    /**
     * Vygeneruje URL pro pojmenovanou route
     */
    public function route(string $name, array $parameters = []): string
    {
        $route = $this->getRouteByName($name);
        if (!$route) {
            throw new \Exception("Route '$name' not found");
        }

        $uri = $route->getUri();

        foreach ($parameters as $key => $value) {
            $uri = str_replace("{{$key}}", $value, $uri);
        }

        return $uri;
    }

    /**
     * Získá všechny pojmenované routes
     */
    public function getNamedRoutes(): array
    {
        return $this->namedRoutes;
    }

    /**
     * Vyčistí všechny routes
     */
    public function clear(): void
    {
        $this->routes = [];
        $this->routeMap = [];
        $this->parameterizedRoutes = [];
        $this->namedRoutes = [];
    }
}
