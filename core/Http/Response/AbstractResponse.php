<?php

namespace Core\Http\Response;

abstract class AbstractResponse
{
    protected mixed $content;
    protected int $statusCode;
    protected array $headers;
    protected string $contentType;

    public function __construct(mixed $content = '', int $statusCode = 200, array $headers = [])
    {
        $this->content = $content;
        $this->statusCode = $statusCode;
        $this->headers = $headers;
        $this->contentType = 'text/html; charset=UTF-8';
    }

    /**
     * Nastaví HTTP header
     */
    public function header(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    /**
     * Nastaví content type
     */
    public function contentType(string $contentType): self
    {
        $this->contentType = $contentType;
        return $this;
    }

    /**
     * Nastaví status kód
     */
    public function status(int $statusCode): self
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    /**
     * Nastaví obsah
     */
    public function content(mixed $content): self
    {
        $this->content = $content;
        return $this;
    }

    /**
     * Získá obsah
     */
    public function getContent(): mixed
    {
        return $this->content;
    }

    /**
     * Získá status kód
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Získá hlavičky
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Odešle response
     */
    public function send(): void
    {
        // Nastav status kód
        http_response_code($this->statusCode);

        // Nastav hlavičky
        foreach ($this->headers as $name => $value) {
            header("$name: $value");
        }

        // Nastav content type
        header("Content-Type: $this->contentType");

        // Vypiš obsah
        echo $this->content;
        exit;
    }
}
