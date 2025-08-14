<?php

namespace Core\Render;

use Core\Authorization\Session;
use Core\Routing\Middleware;
use Core\Routing\Request;
use Doctrine\ORM\EntityManager;
use JetBrains\PhpStorm\NoReturn;

class Renderer
{
    public array $renderedViews = [] {
        get {
            return $this->renderedViews;
        }
        set {
            $this->renderedViews[] = $value;
        }
    }

    protected ?Request $request {
        get => request();
    }
    protected EntityManager $em;

    private static ?Renderer $instance = null;

    public function __construct()
    {
        if (self::$instance === null) {
            self::$instance = $this;
        }
    }


    /**
     * Set HTTP header
     *
     * @param string $header Header to set
     * @return $this
     */
    public function header(string $header): self
    {
        header($header);
        return $this;
    }

    /**
     * Set the content type header
     *
     * @param string $contentType Content type
     * @return $this
     */
    public function contentType(string $contentType): self
    {
        return $this->header("Content-Type: $contentType");
    }

    /**
     * Render a view with data
     *
     * @param string $view View name
     * @param array $data Data to pass to the view
     * @return string Rendered view
     */
    public function view(string $view, array $data = []): string
    {
        $start = microtime(true);
        $output = View::render($view, $data);
        $end = microtime(true);

        // Log rendered view
        $this->renderedViews = [
            'name' => $view,
            'time' => ($end - $start) * 1000, // v ms
            'size' => strlen($output),
            'data' => $data,
        ];

        return $output;
    }

    /**
     * Render HTML content
     *
     * @param string $html HTML content
     * @return string HTML content
     */
    public function html(string $html): string
    {
        $this->contentType('text/html; charset=UTF-8');
        return $html;
    }

    /**
     * Render string content
     *
     * @param string $content String content
     * @return string String content
     */
    public function string(string $content): string
    {
        $this->contentType('text/plain; charset=UTF-8');
        return $content;
    }

    /**
     * Render JSON content
     *
     * @param mixed $data Data to encode as JSON
     * @return string JSON string
     */
    public function json(mixed $data): string
    {
        $this->contentType('application/json; charset=UTF-8');
        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Check if a view exists
     *
     * @param string $view View name
     * @return bool True if view exists
     */
    public function exists(string $view): bool
    {
        return View::exists($view);
    }

    /**
     * Share data with all views
     *
     * @param array|string $key Key or array of key/value pairs
     * @param mixed|null $value Value (if key is string)
     * @return $this
     */
    public function share(array|string $key, mixed $value = null): self
    {
        View::share($key, $value);
        return $this;
    }

    /**
     * Render content and send headers
     *
     * @param string $content Content to render
     * @return void
     */
    public function render(string $content): void
    {
        echo $content;
    }

    public function session(?string $key, mixed $value = null)
    {
        if ($value === null) {
            return $this->getRequest()->getSession()->get($key);

        }
        $this->getRequest()->getSession()->set($key, $value);
        return $this;
    }


    public function getSession(): Session
    {
        return $this->getRequest()->getSession();
    }

    /**
     * Get the current request object
     *
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * Create a redirect response
     *
     * @param string $url URL to redirect to
     * @param int $code HTTP status code
     * @return void
     */
    #[NoReturn] public function redirect(string $url, int $code = 301): void
    {
        $this->statusCode($code);
        $this->header("Location: $url");
    }



    /**
     * Apply middleware to the current request
     *
     * @param string $middleware Middleware method name
     * @return $this
     */
    public function middleware(string $middleware): self
    {
        // Call the middleware method from the Middleware class
        if (method_exists(Middleware::class, $middleware)) {
            Middleware::$middleware();
        }

        return $this;
    }

    /**
     * Apply auth middleware
     *
     * @return $this
     */
    public function auth(): self
    {
        return $this->middleware('auth');
    }

    /**
     * Apply guest middleware
     *
     * @return $this
     */
    public function guest(): self
    {
        return $this->middleware('guest');
    }


    /**
     * Get a query parameter from the request
     *
     * @param string $key Parameter name
     * @param mixed|null $default Default value
     * @return mixed
     */
    public function query(string $key, mixed $default = null): mixed
    {
        return $this->getRequest()->get($key, $default);
    }

    /**
     * Get a post parameter from the request
     *
     * @param string $key Parameter name
     * @param mixed|null $default Default value
     * @return mixed
     */
    public function input(string $key, mixed $default = null): mixed
    {
        return $this->getRequest()->post($key, $default);
    }

    /**
     * Check if the request is an AJAX request
     *
     * @return bool
     */
    public function isAjax(): bool
    {
        return $this->getRequest()->isAjax();
    }

    /**
     * Check if the request is a POST request
     *
     * @return bool
     */
    public function isPost(): bool
    {
        return $this->getRequest()->isPost();
    }

    /**
     * Check if the request is a GET request
     *
     * @return bool
     */
    public function isGet(): bool
    {
        return $this->getRequest()->isGet();
    }

    private function statusCode(int $code): void
    {
        http_response_code($code);
    }
}