<?php

declare(strict_types=1);

namespace SahiCheck\Email;

use SahiCheck\Exception\ApiException;
use SahiCheck\Exception\NetworkException;
use SahiCheck\Response\EmailVerificationResult;
use SahiCheck\SahiCheckClient;

class EmailApi
{
    public function __construct(
        protected readonly SahiCheckClient $client
    ) {}

    /**
     * Verify an email address using the SahiCheck API.
     *
     * @param string $email Email address to verify
     * @return EmailVerificationResult
     * @throws ApiException
     * @throws NetworkException
     */
    public function verify(string $email): EmailVerificationResult
    {
        $payload = [
            'email' => trim($email),
        ];

        $response = $this->client->sendRequest(
            method: 'POST',
            path: '/api/v1/email/verify',
            body: $payload
        );

        return EmailVerificationResult::fromResponse($response);
    }
}
