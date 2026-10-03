<?php

declare(strict_types=1);

namespace SahiCheck\Exception;

use SahiCheck\Http\HttpResponse;
use Throwable;

class ValidationException extends ApiException
{
    /**
     * @param string $message
     * @param int $code
     * @param string|null $requestId
     * @param string|null $errorCode
     * @param array<string, list<string>|string> $details
     * @param HttpResponse|null $response
     * @param array<string, mixed> $raw
     * @param Throwable|null $previous
     */
    public function __construct(
        string $message = 'The given data was invalid.',
        int $code = 422,
        ?string $requestId = null,
        ?string $errorCode = 'VALIDATION_ERROR',
        protected readonly array $details = [],
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
     * @return array<string, list<string>|string>
     */
    public function getDetails(): array
    {
        return $this->details;
    }

    /**
     * Alias for getDetails()
     *
     * @return array<string, list<string>|string>
     */
    public function getErrors(): array
    {
        return $this->details;
    }
}
