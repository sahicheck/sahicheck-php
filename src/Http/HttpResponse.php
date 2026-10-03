<?php

declare(strict_types=1);

namespace SahiCheck\Http;

class HttpResponse
{
    /** @var array<string, string> */
    protected readonly array $normalizedHeaders;

    /** @var array<string, mixed>|null */
    protected ?array $decodedJson = null;

    /**
     * @param int $statusCode
     * @param array<string, string|list<string>> $headers
     * @param string $body
     */
    public function __construct(
        public readonly int $statusCode,
        public readonly array $headers,
        public readonly string $body
    ) {
        $normalized = [];
        foreach ($headers as $key => $value) {
            $normalized[strtolower((string) $key)] = is_array($value) ? implode(', ', $value) : (string) $value;
        }
        $this->normalizedHeaders = $normalized;
    }

    public function isSuccessful(): bool
    {
        return $this->statusCode >= 200 && $this->statusCode < 300;
    }

    public function getHeader(string $name): ?string
    {
        return $this->normalizedHeaders[strtolower($name)] ?? null;
    }

    /**
     * @return array<string, mixed>
     */
    public function json(): array
    {
        if ($this->decodedJson !== null) {
            return $this->decodedJson;
        }

        if (trim($this->body) === '') {
            $this->decodedJson = [];
            return $this->decodedJson;
        }

        $decoded = json_decode($this->body, true);
        if (!is_array($decoded)) {
            $this->decodedJson = [];
            return $this->decodedJson;
        }

        $this->decodedJson = $decoded;
        return $this->decodedJson;
    }

    public function getRequestId(): ?string
    {
        $headerRequestId = $this->getHeader('X-Request-Id');
        if ($headerRequestId !== null && $headerRequestId !== '') {
            return $headerRequestId;
        }

        $data = $this->json();
        if (isset($data['request_id']) && is_string($data['request_id'])) {
            return $data['request_id'];
        }

        return null;
    }
}
