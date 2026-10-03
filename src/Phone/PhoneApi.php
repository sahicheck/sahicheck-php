<?php

declare(strict_types=1);

namespace SahiCheck\Phone;

use SahiCheck\Exception\ApiException;
use SahiCheck\Exception\NetworkException;
use SahiCheck\Response\PhoneVerificationResult;
use SahiCheck\SahiCheckClient;

class PhoneApi
{
    public function __construct(
        protected readonly SahiCheckClient $client
    ) {}

    /**
     * Validate a phone number using the SahiCheck API.
     *
     * @param string $phone Phone number in E.164 or national format (e.g. +919876543210)
     * @param string|null $countryCode Optional two-letter ISO country code (e.g. IN, US)
     * @return PhoneVerificationResult
     * @throws ApiException
     * @throws NetworkException
     */
    public function validate(string $phone, ?string $countryCode = null): PhoneVerificationResult
    {
        $payload = [
            'phone' => $phone,
        ];

        if ($countryCode !== null && trim($countryCode) !== '') {
            $payload['country_code'] = strtoupper(trim($countryCode));
        }

        $response = $this->client->sendRequest(
            method: 'POST',
            path: '/api/v1/phone/validate',
            body: $payload
        );

        return PhoneVerificationResult::fromResponse($response);
    }
}
