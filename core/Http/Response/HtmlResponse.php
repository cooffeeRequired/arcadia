<?php

namespace Core\Http\Response;

class HtmlResponse extends AbstractResponse
{
    public function __construct(string $html = '', int $statusCode = 200, array $headers = [])
    {
        parent::__construct($html, $statusCode, $headers);
        $this->contentType = 'text/html; charset=UTF-8';
    }

    /**
     * Vytvoří HtmlResponse s HTML obsahem
     */
    public static function create(string $html, int $statusCode = 200, array $headers = []): self
    {
        return new self($html, $statusCode, $headers);
    }
}
