<?php

declare(strict_types=1);

namespace SahiCheck\Exception;

use SahiCheck\Http\HttpResponse;
use Throwable;

class RateLimitException extends ApiException
{
    /**
     * @param string $message
     * @param int $code
     * @param string|null $requestId
     * @param string|null $errorCode
     * @param int|null $retryAfter
     * @param int|null $rateLimit
     * @param int|null $rateLimitRemaining
     * @param HttpResponse|null $response
     * @param array<string, mixed> $raw
     * @param Throwable|null $previous
     */
    public function __construct(
        string $message = 'Too many requests. Please try again later.',
        int $code = 429,
        ?string $requestId = null,
        ?string $errorCode = 'RATE_LIMIT_EXCEEDED',
        protected readonly ?int $retryAfter = null,
        protected readonly ?int $rateLimit = null,
        protected readonly ?int $rateLimitRemaining = null,
        ?HttpResponse $response = null,
        array $raw = [],
        ?Throwable $previous = null
    ) {
        parent::__construct(
            message: $message,
            code: $code,
            requestId: $requestId,
            errorCode: $errorCode,
            response: $response,
            raw: $raw,
            previous: $previous
        );
    }

    /**
     * Get the number of seconds to wait before retrying, if provided by Retry-After header.
     */
    public function retryAfter(): ?int
    {
        return $this->retryAfter;
    }

    public function getRetryAfter(): ?int
    {
        return $this->retryAfter;
    }

    public function getRateLimit(): ?int
    {
        return $this->rateLimit;
    }

    public function getRateLimitRemaining(): ?int
    {
        return $this->rateLimitRemaining;
    }
}
