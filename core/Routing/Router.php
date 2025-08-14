<?php /** @noinspection ALL */

namespace Core\Routing;

use App\Controllers\ErrorController;
use Core\Routing\Request;
use Exception;

class Router
{
  protected array $routes = [];
  protected array $errorHandlers = [];
  protected array $middleware = [];
  protected array $groupMiddleware = [];
  protected string $prefix = '';
  protected string $namespace = '';

  /**
   * Přidá routu s middleware
   */
  private function addRoute(string $method, string $uri, callable|array $handler, array $middleware = []): void
  {
    $fullUri = $this->prefix . $uri;
    $this->routes[$method][$fullUri] = $this->resolveHandler($handler);

    // Přidej group middleware
    $allMiddleware = [];
    foreach ($this->groupMiddleware as $groupMiddleware) {
      $allMiddleware = array_merge($allMiddleware, $groupMiddleware);
    }

    // Přidej route-specific middleware
    if (!empty($middleware)) {
      $allMiddleware = array_merge($allMiddleware, $middleware);
    }

    if (!empty($allMiddleware)) {
      $this->middleware[$method][$fullUri] = $allMiddleware;
    }
  }

  public function get(string $uri, callable|array $handler, array $middleware = []): void
  {
    $this->addRoute('GET', $uri, $handler, $middleware);
  }

  public function post(string $uri, callable|array $handler, array $middleware = []): void
  {
    $this->addRoute('POST', $uri, $handler, $middleware);
  }

  public function put(string $uri, callable|array $handler, array $middleware = []): void
  {
    $this->addRoute('PUT', $uri, $handler, $middleware);
  }

  public function delete(string $uri, callable|array $handler, array $middleware = []): void
  {
    $this->addRoute('DELETE', $uri, $handler, $middleware);
  }

  /**
   * Definuje middleware pro skupinu rout
   */
  public function middleware(array $middleware, callable $callback): void
  {
    $this->groupMiddleware[] = $middleware;
    $callback($this);
    array_pop($this->groupMiddleware);
  }

  /**
   * Definuje prefix pro skupinu rout
   */
  public function prefix(string $prefix, callable $callback): void
  {
    $oldPrefix = $this->prefix;
    $this->prefix .= $prefix;
    $callback($this);
    $this->prefix = $oldPrefix;
  }

  /**
   * Definuje namespace pro controllery
   */
  public function namespace(string $namespace, callable $callback): void
  {
    $oldNamespace = $this->namespace;
    $this->namespace .= $namespace;
    $callback($this);
    $this->namespace = $oldNamespace;
  }

  /**
   * Kombinuje middleware, prefix a namespace
   */
  public function group(array $attributes, callable $callback): void
  {
    $oldPrefix = $this->prefix;
    $oldNamespace = $this->namespace;

    if (isset($attributes['prefix'])) {
      $this->prefix .= $attributes['prefix'];
    }

    if (isset($attributes['namespace'])) {
      $this->namespace .= $attributes['namespace'];
    }

    if (isset($attributes['middleware'])) {
      $this->groupMiddleware[] = $attributes['middleware'];
    }

    $callback($this);

    $this->prefix = $oldPrefix;
    $this->namespace = $oldNamespace;

    if (isset($attributes['middleware'])) {
      array_pop($this->groupMiddleware);
    }
  }

  /**
   * Vyřeší handler s namespace
   */
  private function resolveHandler(callable|array $handler): callable|array
  {
    if (is_array($handler) && is_string($handler[0]) && !empty($this->namespace)) {
      $handler[0] = $this->namespace . '\\' . $handler[0];
    }
    return $handler;
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

  /**
   * Spustí middleware pro danou routu
   */
  private function runMiddleware(string $method, string $uri, Request $request, ?array $route = null): void
  {
    $middlewareToRun = [];

    // Přidej route-specific middleware
    if ($route) {
      [$handler, $params] = $route;
      $routeUri = $this->findRouteUri($method, $handler);

      if ($routeUri && isset($this->middleware[$method][$routeUri])) {
        $middlewareToRun = array_merge($middlewareToRun, $this->middleware[$method][$routeUri]);
      }
    }

    debug_log("Found " . count($middlewareToRun) . " middleware to run");
    foreach ($middlewareToRun as $middleware) {
      debug_log("Running middleware: " . $middleware);
      if (method_exists(Middleware::class, $middleware)) {
        Middleware::$middleware($request);

        // V testovacím prostředí nevoláme exit
        if (!defined('APP_ROOT') || !str_contains($_SERVER['SCRIPT_NAME'] ?? '', 'test_')) {
          if (headers_sent() || http_response_code() >= 300) {
            exit;
          }
        }
      }
    }
  }

  /**
   * Najde URI routy podle handleru
   */
  private function findRouteUri(string $method, callable|array $handler): ?string
  {
    if (!isset($this->routes[$method])) {
      return null;
    }

    foreach ($this->routes[$method] as $routeUri => $routeHandler) {
      if ($routeHandler === $handler) {
        return $routeUri;
      }
    }

    return null;
  }

  /**
   * @throws Exception
   */
  public function dispatch(string $uri): void
  {
    $uri = parse_url($uri, PHP_URL_PATH);
    $method = $_SERVER['REQUEST_METHOD'];

    // Vytvoř Request objekt
    $request = Request::getInstance();

    // Najdi routu s parametry
    $route = $this->findRoute($method, $uri);

    if ($route) {
      [$handler, $params] = $route;

      try {
        // Spusť middleware před voláním handleru
        $this->runMiddleware($method, $uri, $request, $route);

        if (is_array($handler)) {
          [$class, $method] = $handler;
          $controller = new $class();

          if (!empty($params)) {
            $result = $controller->$method($request, ...$params);
          } else {
            $result = $controller->$method($request);
          }

          // Pokud metoda vrátila něco, vypiš to
          if ($result !== null && $result !== '') {
            echo $result;
          }
        } else {
          $result = (!empty($params)) ? $handler($request, ...$params) : $handler($request);

          // Pokud handler vrátil něco, vypiš to
          if ($result !== null && $result !== '') {
            echo $result;
          }
        }
      } catch (Exception $e) {
        if (! APP_CONFIGURATION['app_debug']) {
          $this->handleError(500, $e);
        }
        throw $e;
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
  private function handleError(int $statusCode, ?Exception $exception = null): void
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
