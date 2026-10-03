<?php

declare(strict_types=1);

namespace SahiCheck\Ip;

use SahiCheck\Exception\ApiException;
use SahiCheck\Exception\NetworkException;
use SahiCheck\Response\IpLookupResult;
use SahiCheck\SahiCheckClient;

class IpApi
{
    public function __construct(
        protected readonly SahiCheckClient $client
    ) {}

    /**
     * Look up geolocation, ASN, and risk intelligence for an IP address.
     *
     * @param string $ip IPv4 or IPv6 address
     * @return IpLookupResult
     * @throws ApiException
     * @throws NetworkException
     */
    public function lookup(string $ip): IpLookupResult
    {
        $response = $this->client->sendRequest(
            method: 'GET',
            path: '/api/v1/ip/lookup',
            query: ['ip' => trim($ip)]
        );

        return IpLookupResult::fromResponse($response);
    }
}
