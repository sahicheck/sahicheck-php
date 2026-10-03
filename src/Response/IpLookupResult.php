<?php

declare(strict_types=1);

namespace SahiCheck\Response;

use SahiCheck\Http\HttpResponse;

class IpLookupResult extends ApiResponse
{
    /**
     * @param bool $success
     * @param array<string, mixed> $data
     * @param int $creditsUsed
     * @param string|null $requestId
     * @param array<string, mixed> $raw
     * @param bool $valid
     * @param string $ip
     * @param string|null $country
     * @param string|null $countryCode
     * @param string|null $city
     * @param string|null $region
     * @param string|null $asn
     * @param string|null $organization
     * @param string|null $timezone
     * @param bool $vpn
     * @param bool $proxy
     * @param bool $tor
     */
    public function __construct(
        bool $success,
        array $data,
        int $creditsUsed,
        ?string $requestId,
        array $raw,
        public readonly bool $valid,
        public readonly string $ip,
        public readonly ?string $country = null,
        public readonly ?string $countryCode = null,
        public readonly ?string $city = null,
        public readonly ?string $region = null,
        public readonly ?string $asn = null,
        public readonly ?string $organization = null,
        public readonly ?string $timezone = null,
        public readonly bool $vpn = false,
        public readonly bool $proxy = false,
        public readonly bool $tor = false
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
            ip: (string) ($data['ip'] ?? ''),
            country: isset($data['country']) ? (string) $data['country'] : null,
            countryCode: isset($data['country_code']) ? (string) $data['country_code'] : null,
            city: isset($data['city']) ? (string) $data['city'] : null,
            region: isset($data['region']) ? (string) $data['region'] : null,
            asn: isset($data['asn']) ? (string) $data['asn'] : null,
            organization: isset($data['organization']) ? (string) $data['organization'] : null,
            timezone: isset($data['timezone']) ? (string) $data['timezone'] : null,
            vpn: (bool) ($data['vpn'] ?? false),
            proxy: (bool) ($data['proxy'] ?? false),
            tor: (bool) ($data['tor'] ?? false)
        );
    }

    public function __get(string $name): mixed
    {
        if ($name === 'country_code') {
            return $this->countryCode;
        }

        return parent::__get($name);
    }

    public function __isset(string $name): bool
    {
        if ($name === 'country_code') {
            return $this->countryCode !== null;
        }

        return parent::__isset($name);
    }
}
