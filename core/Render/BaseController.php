<?php

namespace Core\Render;

use Core\Facades\Container;
use Core\Routing\Request;
use Doctrine\ORM\EntityManager;
use Exception;
use JetBrains\PhpStorm\NoReturn;

abstract class BaseController
{
    protected ?EntityManager $em {
        get => Container::get('doctrine.em');
    }
    protected ?Renderer $renderer {
        get => Container::get(Renderer::class);
    }

    /**
     * @throws Exception
     */
    public function __construct()
    {
        if (is_null($this->renderer)) {
            throw new Exception('Renderer is not initialized');
        }
        if (is_null($this->em)) {
            throw new Exception('EntityManager is not initialized');
        }
    }

    protected function render(string $content): void
    {
        $this->renderer->render($content);
    }

    protected function view(string $view, array $data = []): string
    {
        return $this->renderer->view($view, $data);
    }

    protected function renderView(string $view, array $data = []): void
    {
        $content = $this->renderer->view($view, $data);
        $this->renderer->render($content);
    }

    protected function html(string $html): string
    {
        return $this->renderer->html($html);
    }

    protected function string(string $content): string
    {
        return $this->renderer->string($content);
    }

    protected function json(mixed $data): string
    {
        return $this->renderer->json($data);
    }

    #[NoReturn]
    protected function redirect(string $url, int $code = 302): void
    {
        $this->renderer->redirect($url, $code);
    }

    protected function query(string $key, mixed $default = null): mixed
    {
        return $this->renderer->query($key, $default);
    }

    protected function input(string $key, mixed $default = null): mixed
    {
        return $this->renderer->input($key, $default);
    }

    protected function session(?string $key, mixed $value = null)
    {
        return $this->renderer->session($key, $value);
    }
    protected function isAjax(): bool
    {
        return $this->renderer->isAjax();
    }

    protected function isPost(): bool
    {
        return $this->renderer->isPost();
    }

    protected function isGet(): bool
    {
        return $this->renderer->isGet();
    }

    protected function viewExists(string $view): bool
    {
        return $this->renderer->exists($view);
    }

    /**
     * Share data with all views
     */
    protected function share(array|string $key, mixed $value = null): self
    {
        $this->renderer->share($key, $value);
        return $this;
    }


    /**
     * Set HTTP header
     */
    protected function header(string $header): self
    {
        $this->renderer->header($header);
        return $this;
    }

    protected function getRequest(): Request
    {
        return $this->renderer->getRequest();
    }

    protected function getRenderedViews(): array
    {
        return $this->renderer;
    }

    protected function contentType(string $contentType): self
    {
        $this->renderer->contentType($contentType);
        return $this;
    }

    protected function status(int $code): self
    {
        $this->renderer->status($code);
        return $this;
    }

    protected function middleware(string $middleware): self
    {
        $this->renderer->middleware($middleware);
        return $this;
    }

}
