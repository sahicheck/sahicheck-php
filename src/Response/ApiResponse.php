<?php

declare(strict_types=1);

namespace SahiCheck\Response;

abstract class ApiResponse
{
    /**
     * @param bool $success
     * @param array<string, mixed> $data
     * @param int $creditsUsed
     * @param string|null $requestId
     * @param array<string, mixed> $raw
     */
    public function __construct(
        public readonly bool $success,
        public readonly array $data,
        public readonly int $creditsUsed,
        public readonly ?string $requestId,
        public readonly array $raw
    ) {}

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function getCreditsUsed(): int
    {
        return $this->creditsUsed;
    }

    public function getRequestId(): ?string
    {
        return $this->requestId;
    }

    /**
     * @return array<string, mixed>
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Returns the underlying raw API response array.
     *
     * @return array<string, mixed>
     */
    public function raw(): array
    {
        return $this->raw;
    }

    /**
     * Allows dynamic access to snake_case aliases and underlying data keys.
     *
     * @param string $name
     * @return mixed
     */
    public function __get(string $name): mixed
    {
        if ($name === 'credits_used') {
            return $this->creditsUsed;
        }

        if ($name === 'request_id') {
            return $this->requestId;
        }

        return $this->data[$name] ?? null;
    }

    public function __isset(string $name): bool
    {
        if ($name === 'credits_used' || $name === 'request_id') {
            return true;
        }

        return isset($this->data[$name]);
    }
}
