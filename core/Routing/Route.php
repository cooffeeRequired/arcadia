<?php

namespace Core\Routing;

class Route
{
    private array $methods;
    private string $uri;
    private mixed $action;
    private array $middleware = [];
    private array $parameters = [];
    private ?string $name = null;

    public function __construct(array $methods, string $uri, callable|array $action)
    {
        $this->methods = $methods;
        $this->uri = $uri;
        $this->action = $action;
    }

    /**
     * Zkontroluje, zda route odpovídá danému URI
     */
    public function matches(string $uri): bool
    {
        $pattern = $this->convertToRegex();
        if (preg_match($pattern, $uri, $matches)) {
            array_shift($matches); // Odstraň celou shodu
            $paramNames = $this->getParameterNames();
            $this->parameters = [];
            foreach ($paramNames as $index => $name) {
                if (isset($matches[$index])) {
                    $this->parameters[$name] = $matches[$index];
                }
            }
            return true;
        }
        return false;
    }

    /**
     * Převede URI pattern na regex
     */
    private function convertToRegex(): string
    {
        return '#^' . preg_replace('#\{([a-zA-Z]+)}#', '([^/]+)', $this->uri) . '$#';
    }

    /**
     * Získá názvy parametrů z URI patternu
     */
    private function getParameterNames(): array
    {
        preg_match_all('#\{([a-zA-Z]+)}#', $this->uri, $matches);
        return $matches[1] ?? [];
    }

    /**
     * Přidá middleware
     */
    public function middleware(array|string $middleware): self
    {
        if (is_string($middleware)) {
            $this->middleware[] = $middleware;
        } elseif (is_array($middleware) && !empty($middleware)) {
            $this->middleware = array_merge($this->middleware, $middleware);
        }
        return $this;
    }

    /**
     * Získá metody
     */
    public function getMethods(): array
    {
        return $this->methods;
    }

    /**
     * Získá URI
     */
    public function getUri(): string
    {
        return $this->uri;
    }

    /**
     * Získá action
     */
    public function getAction(): callable|array
    {
        return $this->action;
    }

    /**
     * Získá middleware
     */
    public function getMiddleware(): array
    {
        return $this->middleware;
    }

    /**
     * Získá parametry
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * Získá parametr podle názvu
     */
    public function getParameter(string $name, mixed $default = null): mixed
    {
        return $this->parameters[$name] ?? $default;
    }

    /**
     * Nastaví název route
     */
    public function name(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Získá název route
     */
    public function getName(): ?string
    {
        return $this->name;
    }
}
