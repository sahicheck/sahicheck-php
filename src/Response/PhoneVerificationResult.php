<?php

declare(strict_types=1);

namespace SahiCheck\Response;

use SahiCheck\Http\HttpResponse;

class PhoneVerificationResult extends ApiResponse
{
    /**
     * @param bool $success
     * @param array<string, mixed> $data
     * @param int $creditsUsed
     * @param string|null $requestId
     * @param array<string, mixed> $raw
     * @param bool $valid
     * @param string $phone
     * @param string|null $country
     * @param string|null $callingCode
     * @param string|null $nationalFormat
     * @param string|null $internationalFormat
     * @param string|null $type
     * @param string|null $carrier
     */
    public function __construct(
        bool $success,
        array $data,
        int $creditsUsed,
        ?string $requestId,
        array $raw,
        public readonly bool $valid,
        public readonly string $phone,
        public readonly ?string $country = null,
        public readonly ?string $callingCode = null,
        public readonly ?string $nationalFormat = null,
        public readonly ?string $internationalFormat = null,
        public readonly ?string $type = null,
        public readonly ?string $carrier = null
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
            phone: (string) ($data['phone'] ?? ''),
            country: isset($data['country']) ? (string) $data['country'] : null,
            callingCode: isset($data['calling_code']) ? (string) $data['calling_code'] : null,
            nationalFormat: isset($data['national_format']) ? (string) $data['national_format'] : null,
            internationalFormat: isset($data['international_format']) ? (string) $data['international_format'] : null,
            type: isset($data['type']) ? (string) $data['type'] : null,
            carrier: isset($data['carrier']) ? (string) $data['carrier'] : null
        );
    }

    public function __get(string $name): mixed
    {
        return match ($name) {
            'calling_code' => $this->callingCode,
            'national_format' => $this->nationalFormat,
            'international_format' => $this->internationalFormat,
            default => parent::__get($name),
        };
    }

    public function __isset(string $name): bool
    {
        return match ($name) {
            'calling_code' => $this->callingCode !== null,
            'national_format' => $this->nationalFormat !== null,
            'international_format' => $this->internationalFormat !== null,
            default => parent::__isset($name),
        };
    }
}
