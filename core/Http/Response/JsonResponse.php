<?php

namespace Core\Http\Response;

class JsonResponse extends AbstractResponse
{
    public function __construct(mixed $data = null, int $statusCode = 200, array $headers = [])
    {
        $jsonContent = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        parent::__construct($jsonContent, $statusCode, $headers);
        $this->contentType = 'application/json; charset=UTF-8';
    }

    /**
     * Vytvoří JsonResponse s JSON obsahem
     */
    public static function create(mixed $data, int $statusCode = 200, array $headers = []): self
    {
        return new self($data, $statusCode, $headers);
    }

    /**
     * Vytvoří JsonResponse s chybou
     */
    public static function error(string $message, int $statusCode = 400, array $headers = []): self
    {
        return new self(['error' => $message], $statusCode, $headers);
    }

    /**
     * Vytvoří JsonResponse s úspěchem
     */
    public static function success(mixed $data = null, string $message = 'Success', int $statusCode = 200, array $headers = []): self
    {
        return new self([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $statusCode, $headers);
    }
}
