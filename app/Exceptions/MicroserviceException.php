<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Client\Response;

class MicroserviceException extends Exception
{
    /**
     * Full error payload returned by the microservice.
     */
    protected array $payload;

    /**
     * HTTP status code from the microservice response.
     */
    protected int $statusCode;

    /**
     * Optional headers to pass through (e.g., Retry-After).
     */
    protected array $headers;

    /**
     * Create a new MicroserviceException instance.
     */
    public function __construct(array $payload, int $statusCode = 400, array $headers = [])
    {
        parent::__construct($payload['message'] ?? 'Microservice Error', $statusCode);

        $this->payload = $payload;
        $this->statusCode = $statusCode;
        $this->headers = $headers;
    }

    /**
     * Build from an Illuminate HTTP Response (keeps JSON if possible).
     */
    public static function fromResponse(Response $response, array $headers = []): self
    {
        $status = $response->status();

        // Attempt to keep JSON body from downstream
        $payload = null;
        try {
            $payload = $response->json();
        } catch (\Throwable) {
            $payload = null;
        }

        if (! is_array($payload)) {
            $payload = [
                'message' => $response->reason() ?: "HTTP {$status}",
                'body' => $response->body(),
            ];
        }

        return new self($payload, $status, $headers);
    }

    public function getPayload(): array
    {
        return $this->payload;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }
}
