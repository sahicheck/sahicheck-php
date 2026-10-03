<?php

declare(strict_types=1);

namespace SahiCheck\Http;

use SahiCheck\Exception\NetworkException;

interface HttpClientInterface
{
    /**
     * Send an HTTP request and return an HttpResponse.
     *
     * @param string $method HTTP method (GET, POST, etc.)
     * @param string $url Target URL
     * @param array<string, string> $headers Key-value map of HTTP headers
     * @param array<string, mixed>|null $body Request payload (will be JSON-encoded if provided)
     * @param int $timeout Request timeout in seconds
     * @return HttpResponse
     * @throws NetworkException On connection error, timeout, or DNS failure
     */
    public function send(
        string $method,
        string $url,
        array $headers = [],
        ?array $body = null,
        int $timeout = 10
    ): HttpResponse;
}
