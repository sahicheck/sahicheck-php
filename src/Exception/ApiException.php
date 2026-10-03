<?php

declare(strict_types=1);

namespace SahiCheck\Exception;

use SahiCheck\Http\HttpResponse;
use Throwable;

class ApiException extends SahiCheckException
{
    /**
     * @param string $message
     * @param int $code
     * @param string|null $requestId
     * @param string|null $errorCode
     * @param HttpResponse|null $response
     * @param array<string, mixed> $raw
     * @param Throwable|null $previous
     */
    public function __construct(
        string $message = '',
        int $code = 0,
        ?string $requestId = null,
        ?string $errorCode = null,
        protected readonly ?HttpResponse $response = null,
        protected readonly array $raw = [],
        ?Throwable $previous = null
    ) {
        parent::__construct(
            message: $message,
            code: $code,
            requestId: $requestId,
            errorCode: $errorCode,
            previous: $previous
        );
    }

    public function getResponse(): ?HttpResponse
    {
        return $this->response;
    }

    /**
     * @return array<string, mixed>
     */
    public function getRaw(): array
    {
        return $this->raw;
    }

    public static function fromResponse(HttpResponse $response): self
    {
        $payload = $response->json();
        $error = is_array($payload['error'] ?? null) ? $payload['error'] : [];

        $message = is_string($error['message'] ?? null) && trim($error['message']) !== ''
            ? $error['message']
            : sprintf('SahiCheck API responded with HTTP status %d.', $response->statusCode);

        $errorCode = is_string($error['code'] ?? null) ? $error['code'] : null;
        $requestId = $response->getRequestId();
        $status = $response->statusCode;

        return match ($status) {
            401 => new AuthenticationException(
                message: $message,
                code: $status,
                requestId: $requestId,
                errorCode: $errorCode ?? 'UNAUTHORIZED',
                response: $response,
                raw: $payload
            ),
            402 => new InsufficientCreditsException(
                message: $message,
                code: $status,
                requestId: $requestId,
                errorCode: $errorCode ?? 'INSUFFICIENT_CREDITS',
                response: $response,
                raw: $payload
            ),
            422 => new ValidationException(
                message: $message,
                code: $status,
                requestId: $requestId,
                errorCode: $errorCode ?? 'VALIDATION_ERROR',
                details: is_array($error['details'] ?? null) ? $error['details'] : [],
                response: $response,
                raw: $payload
            ),
            429 => new RateLimitException(
                message: $message,
                code: $status,
                requestId: $requestId,
                errorCode: $errorCode ?? 'RATE_LIMIT_EXCEEDED',
                retryAfter: ($retryAfter = $response->getHeader('Retry-After')) !== null ? (int) $retryAfter : null,
                rateLimit: ($limit = $response->getHeader('X-RateLimit-Limit')) !== null ? (int) $limit : null,
                rateLimitRemaining: ($remaining = $response->getHeader('X-RateLimit-Remaining')) !== null ? (int) $remaining : null,
                response: $response,
                raw: $payload
            ),
            500, 502, 503, 504 => new ServerException(
                message: $message,
                code: $status,
                requestId: $requestId,
                errorCode: $errorCode ?? ($status === 502 ? 'PROVIDER_ERROR' : 'SERVER_ERROR'),
                response: $response,
                raw: $payload
            ),
            default => new self(
                message: $message,
                code: $status,
                requestId: $requestId,
                errorCode: $errorCode,
                response: $response,
                raw: $payload
            ),
        };
    }
}
