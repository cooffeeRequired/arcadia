<?php

namespace Core\Http;

use Core\Http\Response\AbstractResponse;
use Core\Http\Response\HtmlResponse;
use Core\Http\Response\JsonResponse;
use Core\Http\Response\RedirectResponse;
use Core\Http\Response\ViewResponse;
use Core\Render\View;

/**
 * @deprecated Použijte specializované Response třídy místo této
 */
class Response extends AbstractResponse
{
    public function __construct(mixed $content = '', int $statusCode = 200, array $headers = [])
    {
        parent::__construct($content, $statusCode, $headers);
    }

    /**
     * @deprecated Použijte HtmlResponse::create()
     */
    public static function html(string $html, int $statusCode = 200, array $headers = []): self
    {
        return new self($html, $statusCode, $headers);
    }

    /**
     * @deprecated Použijte JsonResponse::create()
     */
    public static function json(mixed $data, int $statusCode = 200, array $headers = []): self
    {
        $jsonContent = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        return new self($jsonContent, $statusCode, $headers);
    }

    /**
     * @deprecated Použijte ViewResponse::create()
     */
    public static function view(string $view, array $data = [], int $statusCode = 200, array $headers = []): self
    {
        $content = View::render($view, $data);
        return new self($content, $statusCode, $headers);
    }

    /**
     * @deprecated Použijte RedirectResponse::create()
     */
    public static function redirect(string $url, int $statusCode = 302, array $headers = []): void
    {
        $headers['Location'] = $url;
        $response = new self('', $statusCode, $headers);
        $response->send();
    }

    /**
     * @deprecated Použijte specializované error metody
     */
    public static function error(int $statusCode, string $message = null): self
    {
        $errorData = [
            'code' => $statusCode,
            'title' => self::getErrorTitle($statusCode),
            'message' => $message ?? self::getErrorMessage($statusCode)
        ];

        $content = View::render('errors.generic', $errorData);
        return new self($content, $statusCode);
    }

    /**
     * @deprecated Použijte specializované error metody
     */
    public static function notFound(string $message = null): self
    {
        return self::error(404, $message);
    }

    /**
     * @deprecated Použijte specializované error metody
     */
    public static function forbidden(string $message = null): self
    {
        return self::error(403, $message);
    }

    /**
     * @deprecated Použijte specializované error metody
     */
    public static function serverError(string $message = null): self
    {
        return self::error(500, $message);
    }

    /**
     * @deprecated Použijte specializované error metody
     */
    public static function badRequest(string $message = null): self
    {
        return self::error(400, $message);
    }

    /**
     * @deprecated Použijte specializované error metody
     */
    public static function unauthorized(string $message = null): self
    {
        return self::error(401, $message);
    }

    /**
     * @deprecated Použijte specializované error metody
     */
    public static function methodNotAllowed(string $message = null): self
    {
        return self::error(405, $message);
    }

    /**
     * @deprecated Použijte specializované error metody
     */
    public static function requestTimeout(string $message = null): self
    {
        return self::error(408, $message);
    }

    /**
     * @deprecated Použijte specializované error metody
     */
    public static function tooManyRequests(string $message = null): self
    {
        return self::error(429, $message);
    }

    /**
     * @deprecated Použijte specializované error metody
     */
    public static function badGateway(string $message = null): self
    {
        return self::error(502, $message);
    }

    /**
     * @deprecated Použijte specializované error metody
     */
    public static function serviceUnavailable(string $message = null): self
    {
        return self::error(503, $message);
    }

    /**
     * @deprecated Použijte specializované error metody
     */
    public static function gatewayTimeout(string $message = null): self
    {
        return self::error(504, $message);
    }

    /**
     * Získá nadpis pro daný status kód
     */
    private static function getErrorTitle(int $statusCode): string
    {
        return match($statusCode) {
            400 => 'Špatný požadavek',
            401 => 'Neautorizovaný přístup',
            403 => 'Přístup zamítnut',
            404 => 'Stránka nenalezena',
            405 => 'Metoda není povolena',
            408 => 'Časový limit vypršel',
            429 => 'Příliš mnoho požadavků',
            500 => 'Interní chyba serveru',
            502 => 'Špatná brána',
            503 => 'Služba není dostupná',
            504 => 'Časový limit brány',
            default => 'Chyba'
        };
    }

    /**
     * Získá výchozí zprávu pro daný status kód
     */
    private static function getErrorMessage(int $statusCode): string
    {
        return match($statusCode) {
            400 => 'Požadavek obsahuje neplatné údaje.',
            401 => 'Pro přístup k této stránce se musíte přihlásit.',
            403 => 'Nemáte oprávnění k přístupu k této stránce.',
            404 => 'Požadovaná stránka nebyla nalezena.',
            405 => 'Použitá metoda není pro tuto stránku povolena.',
            408 => 'Časový limit požadavku vypršel.',
            429 => 'Příliš mnoho požadavků. Zkuste to prosím později.',
            500 => 'Došlo k neočekávané chybě na serveru.',
            502 => 'Server dostal neplatnou odpověď od upstream serveru.',
            503 => 'Služba je dočasně nedostupná.',
            504 => 'Časový limit brány vypršel.',
            default => 'Došlo k neočekávané chybě.'
        };
    }
}
