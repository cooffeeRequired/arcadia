<?php

namespace Core\Http\Response;

class RedirectResponse extends AbstractResponse
{
    public function __construct(string $url, int $statusCode = 302, array $headers = [])
    {
        $headers['Location'] = $url;
        parent::__construct('', $statusCode, $headers);
        $this->contentType = 'text/html; charset=UTF-8';
    }

    /**
     * Vytvoří RedirectResponse
     */
    public static function create(string $url, int $statusCode = 302, array $headers = []): self
    {
        return new self($url, $statusCode, $headers);
    }

    /**
     * Vytvoří RedirectResponse s flash zprávou
     */
    public static function withMessage(string $url, string $message, string $type = 'info', int $statusCode = 302): self
    {
        // Flash zpráva se nastaví přes session v controlleru
        return new self($url, $statusCode);
    }
}
