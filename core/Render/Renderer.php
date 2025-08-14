<?php

namespace Core\Render;

use Core\Http\Response\HtmlResponse;
use Core\Http\Response\JsonResponse;
use Core\Http\Response\RedirectResponse;
use Core\Http\Response\ViewResponse;
use Core\Http\Response\AbstractResponse;

class Renderer
{
    /**
     * Renderuje view a vrací ViewResponse
     */
    public function view(string $view, array $data = [], int $statusCode = 200, array $headers = []): ViewResponse
    {
        return ViewResponse::create($view, $data, $statusCode, $headers);
    }

    /**
     * Renderuje HTML a vrací HtmlResponse
     */
    public function html(string $html, int $statusCode = 200, array $headers = []): HtmlResponse
    {
        return HtmlResponse::create($html, $statusCode, $headers);
    }

    /**
     * Renderuje JSON a vrací JsonResponse
     */
    public function json(mixed $data, int $statusCode = 200, array $headers = []): JsonResponse
    {
        return JsonResponse::create($data, $statusCode, $headers);
    }

    /**
     * Vytvoří redirect a vrací RedirectResponse
     */
    public function redirect(string $url, int $statusCode = 302, array $headers = []): RedirectResponse
    {
        return RedirectResponse::create($url, $statusCode, $headers);
    }

    /**
     * Vytvoří JSON error response
     */
    public function jsonError(string $message, int $statusCode = 400, array $headers = []): JsonResponse
    {
        return JsonResponse::error($message, $statusCode, $headers);
    }

    /**
     * Vytvoří JSON success response
     */
    public function jsonSuccess(mixed $data = null, string $message = 'Success', int $statusCode = 200, array $headers = []): JsonResponse
    {
        return JsonResponse::success($data, $message, $statusCode, $headers);
    }

    /**
     * Zkontroluje, zda view existuje
     */
    public function viewExists(string $view): bool
    {
        return View::exists($view);
    }

    /**
     * Sdílí data se všemi views
     */
    public function share(array|string $key, mixed $value = null): self
    {
        View::share($key, $value);
        return $this;
    }
}
