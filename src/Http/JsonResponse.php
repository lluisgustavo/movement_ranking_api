<?php

declare(strict_types=1);

namespace App\Http;

class JsonResponse
{
    private int $statusCode;
    private array $headers;
    private mixed $data;

    public function __construct(
        mixed $data = null,
        int $statusCode = 200,
        array $headers = []
    ) {
        $this->data = $data;
        $this->statusCode = $statusCode;
        $this->headers = $headers;
    }

    public function send(): void
    {
        http_response_code($this->statusCode);

        header('Content-Type: application/json; charset=utf-8');

        foreach ($this->headers as $name => $value) {
            header("$name: $value");
        }

        if ($this->data !== null) {
            echo json_encode(
                $this->data,
                JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
            );
        }
    }

    public static function success(mixed $data, int $statusCode = 200): self
    {
        return new self($data, $statusCode);
    }

    public static function error(string $message, int $statusCode): self
    {
        return new self(['error' => $message], $statusCode);
    }
}