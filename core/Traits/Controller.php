<?php

namespace Core\Traits;

use Core\Facades\Container;
use Core\Render\Renderer;
use Core\Routing\Request;

trait Controller
{
    protected ?Renderer $renderer = null;

    /**
     * Inicializuje renderer při prvním použití
     */
    private function initRenderer(): void
    {
        if ($this->renderer === null) {
            $this->renderer = Container::get(Renderer::class);
            if ($this->renderer === null) {
                throw new \RuntimeException("Renderer not found in container.");
            }
            $this->renderer->handle();
        }
    }

    /**
     * Render content and send headers
     */
    protected function render(string $content): void
    {
        $this->initRenderer();
        $this->renderer->render($content);
    }

    /**
     * Render a view with data
     */
    protected function view(string $view, array $data = []): string
    {
        $this->initRenderer();
        return $this->renderer->view($view, $data);
    }

    /**
     * Render HTML content
     */
    protected function html(string $html): string
    {
        $this->initRenderer();
        return $this->renderer->html($html);
    }

    /**
     * Render string content
     */
    protected function string(string $content): string
    {
        $this->initRenderer();
        return $this->renderer->string($content);
    }

    /**
     * Render JSON content
     */
    protected function json(mixed $data): string
    {
        $this->initRenderer();
        return $this->renderer->json($data);
    }

    /**
     * Set HTTP header
     */
    protected function header(string $header): self
    {
        $this->initRenderer();
        $this->renderer->header($header);
        return $this;
    }

    /**
     * Set content type header
     */
    protected function contentType(string $contentType): self
    {
        $this->initRenderer();
        $this->renderer->contentType($contentType);
        return $this;
    }

    /**
     * Set HTTP status code
     */
    protected function status(int $code): self
    {
        $this->initRenderer();
        $this->renderer->status($code);
        return $this;
    }

    /**
     * Apply middleware
     */
    protected function middleware(string $middleware): self
    {
        $this->initRenderer();
        $this->renderer->middleware($middleware);
        return $this;
    }

    /**
     * Apply auth middleware
     */
    protected function auth(): self
    {
        $this->initRenderer();
        $this->renderer->auth();
        return $this;
    }

    /**
     * Apply guest middleware
     */
    protected function guest(): self
    {
        $this->initRenderer();
        $this->renderer->guest();
        return $this;
    }

    /**
     * Apply admin middleware
     */
    protected function admin(): self
    {
        $this->initRenderer();
        $this->renderer->admin();
        return $this;
    }

    /**
     * Create a redirect response
     */
    protected function redirect(string $url, int $code = 302): void
    {
        $this->initRenderer();
        $this->renderer->redirect($url, $code);
    }

    /**
     * Get the current request object
     */
    protected function getRequest(): Request
    {
        $this->initRenderer();
        return $this->renderer->getRequest();
    }

    /**
     * Get a query parameter from the request
     */
    protected function query(string $key, mixed $default = null): mixed
    {
        $this->initRenderer();
        return $this->renderer->query($key, $default);
    }

    /**
     * Get a post parameter from the request
     */
    protected function input(string $key, mixed $default = null): mixed
    {
        $this->initRenderer();
        return $this->renderer->input($key, $default);
    }

    /**
     * Session helper
     */
    protected function session(?string $key, mixed $value = null)
    {
        $this->initRenderer();
        return $this->renderer->session($key, $value);
    }

    /**
     * Check if the request is an AJAX request
     */
    protected function isAjax(): bool
    {
        $this->initRenderer();
        return $this->renderer->isAjax();
    }

    /**
     * Check if the request is a POST request
     */
    protected function isPost(): bool
    {
        $this->initRenderer();
        return $this->renderer->isPost();
    }

    /**
     * Check if the request is a GET request
     */
    protected function isGet(): bool
    {
        $this->initRenderer();
        return $this->renderer->isGet();
    }

    /**
     * Check if a view exists
     */
    protected function viewExists(string $view): bool
    {
        $this->initRenderer();
        return $this->renderer->exists($view);
    }

    /**
     * Share data with all views
     */
    protected function share(array|string $key, mixed $value = null): self
    {
        $this->initRenderer();
        $this->renderer->share($key, $value);
        return $this;
    }

    /**
     * Get all rendered views
     */
    protected function getRenderedViews(): array
    {
        $this->initRenderer();
        return $this->renderer->getRenderedViews();
    }
}