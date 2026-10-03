<?php

declare(strict_types=1);

namespace SahiCheck\Exception;

use RuntimeException;
use Throwable;

class SahiCheckException extends RuntimeException
{
    public function __construct(
        string $message = '',
        int $code = 0,
        protected readonly ?string $requestId = null,
        protected readonly ?string $errorCode = null,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function getRequestId(): ?string
    {
        return $this->requestId;
    }

    public function getErrorCode(): ?string
    {
        return $this->errorCode;
    }
}
