<?php

declare(strict_types=1);

namespace SahiCheck\Response;

use SahiCheck\Http\HttpResponse;

class EmailVerificationResult extends ApiResponse
{
    /**
     * @param bool $success
     * @param array<string, mixed> $data
     * @param int $creditsUsed
     * @param string|null $requestId
     * @param array<string, mixed> $raw
     * @param bool $valid
     * @param string $email
     * @param string|null $user
     * @param string|null $domain
     * @param bool $syntax
     * @param bool $mx
     * @param bool $disposable
     * @param bool $role
     * @param bool $free
     * @param float|int|null $score
     */
    public function __construct(
        bool $success,
        array $data,
        int $creditsUsed,
        ?string $requestId,
        array $raw,
        public readonly bool $valid,
        public readonly string $email,
        public readonly ?string $user = null,
        public readonly ?string $domain = null,
        public readonly bool $syntax = false,
        public readonly bool $mx = false,
        public readonly bool $disposable = false,
        public readonly bool $role = false,
        public readonly bool $free = false,
        public readonly float|int|null $score = null
    ) {
        parent::__construct(
            success: $success,
            data: $data,
            creditsUsed: $creditsUsed,
            requestId: $requestId,
            raw: $raw
        );
    }

    public static function fromResponse(HttpResponse $response): self
    {
        $payload = $response->json();
        $data = is_array($payload['data'] ?? null) ? $payload['data'] : [];

        return new self(
            success: (bool) ($payload['success'] ?? false),
            data: $data,
            creditsUsed: (int) ($payload['credits_used'] ?? 0),
            requestId: (string) ($payload['request_id'] ?? $response->getRequestId() ?? ''),
            raw: $payload,
            valid: (bool) ($data['valid'] ?? false),
            email: (string) ($data['email'] ?? ''),
            user: isset($data['user']) ? (string) $data['user'] : null,
            domain: isset($data['domain']) ? (string) $data['domain'] : null,
            syntax: (bool) ($data['syntax'] ?? false),
            mx: (bool) ($data['mx'] ?? false),
            disposable: (bool) ($data['disposable'] ?? false),
            role: (bool) ($data['role'] ?? false),
            free: (bool) ($data['free'] ?? false),
            score: isset($data['score']) && is_numeric($data['score']) ? (float) $data['score'] : null
        );
    }
}
