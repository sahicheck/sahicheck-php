<?php

declare(strict_types=1);

namespace SahiCheck\Exception;

use Throwable;

class NetworkException extends SahiCheckException
{
    public function __construct(
        string $message = 'Network error occurred while communicating with SahiCheck API.',
        int $code = 0,
        ?string $requestId = null,
        ?Throwable $previous = null
    ) {
        parent::__construct(
            message: $message,
            code: $code,
            requestId: $requestId,
            errorCode: 'NETWORK_ERROR',
            previous: $previous
        );
    }
}
