<?php

declare(strict_types=1);

namespace SahiCheck;

use SahiCheck\Email\EmailApi;
use SahiCheck\Exception\ApiException;
use SahiCheck\Exception\NetworkException;
use SahiCheck\Http\CurlHttpClient;
use SahiCheck\Http\HttpClientInterface;
use SahiCheck\Http\HttpResponse;
use SahiCheck\Ip\IpApi;
use SahiCheck\Phone\PhoneApi;

class SahiCheckClient
{
    public const DEFAULT_BASE_URL = 'https://api.sahicheck.com';
    public const DEFAULT_TIMEOUT = 10;

    protected readonly string $apiKey;
    protected readonly string $baseUrl;
    protected readonly int $timeout;
    protected readonly HttpClientInterface $httpClient;
    protected readonly ?string $requestId;

    protected ?PhoneApi $phoneApi = null;
    protected ?EmailApi $emailApi = null;
    protected ?IpApi $ipApi = null;

    /**
     * @param string $apiKey The SahiCheck API key
     * @param string $baseUrl Base URL for the SahiCheck API
     * @param int $timeout HTTP timeout in seconds (defaults to 10)
     * @param HttpClientInterface|null $httpClient Custom HTTP client implementation
     * @param string|null $requestId Optional client-provided request ID
     */
    public function __construct(
        string $apiKey,
        string $baseUrl = self::DEFAULT_BASE_URL,
        int $timeout = self::DEFAULT_TIMEOUT,
        ?HttpClientInterface $httpClient = null,
        ?string $requestId = null
    ) {
        $this->apiKey = trim($apiKey);
        $this->baseUrl = rtrim(trim($baseUrl), '/');
        $this->timeout = max(1, $timeout);
        $this->httpClient = $httpClient ?? new CurlHttpClient();
        $this->requestId = $requestId !== null ? trim($requestId) : null;
    }

    /**
     * Returns an instance configured with a custom client request ID.
     */
    public function withRequestId(?string $requestId): self
    {
        return new self(
            apiKey: $this->apiKey,
            baseUrl: $this->baseUrl,
            timeout: $this->timeout,
            httpClient: $this->httpClient,
            requestId: $requestId
        );
    }

    /**
     * Returns an instance configured with an updated timeout.
     */
    public function withTimeout(int $timeout): self
    {
        return new self(
            apiKey: $this->apiKey,
            baseUrl: $this->baseUrl,
            timeout: $timeout,
            httpClient: $this->httpClient,
            requestId: $this->requestId
        );
    }

    /**
     * Returns an instance configured with an updated base URL.
     */
    public function withBaseUrl(string $baseUrl): self
    {
        return new self(
            apiKey: $this->apiKey,
            baseUrl: $baseUrl,
            timeout: $this->timeout,
            httpClient: $this->httpClient,
            requestId: $this->requestId
        );
    }

    /**
     * Access the Phone Verification API.
     */
    public function phone(): PhoneApi
    {
        if ($this->phoneApi === null) {
            $this->phoneApi = new PhoneApi($this);
        }

        return $this->phoneApi;
    }

    /**
     * Access the Email Verification API.
     */
    public function email(): EmailApi
    {
        if ($this->emailApi === null) {
            $this->emailApi = new EmailApi($this);
        }

        return $this->emailApi;
    }

    /**
     * Access the IP Intelligence API.
     */
    public function ip(): IpApi
    {
        if ($this->ipApi === null) {
            $this->ipApi = new IpApi($this);
        }

        return $this->ipApi;
    }

    /**
     * Send an authenticated API request and handle errors.
     *
     * @param string $method HTTP method (GET, POST, etc.)
     * @param string $path Path relative to base URL (e.g. /api/v1/phone/validate)
     * @param array<string, mixed> $query Query parameters
     * @param array<string, mixed>|null $body Request payload
     * @return HttpResponse
     * @throws ApiException
     * @throws NetworkException
     */
    public function sendRequest(
        string $method,
        string $path,
        array $query = [],
        ?array $body = null
    ): HttpResponse {
        $url = $this->baseUrl . '/' . ltrim($path, '/');

        if (!empty($query)) {
            $queryString = http_build_query($query);
            $separator = str_contains($url, '?') ? '&' : '?';
            $url .= $separator . $queryString;
        }

        $headers = [
            'X-API-Key' => $this->apiKey,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];

        if ($this->requestId !== null && $this->requestId !== '') {
            $headers['X-Request-Id'] = $this->requestId;
        }

        $response = $this->httpClient->send(
            method: $method,
            url: $url,
            headers: $headers,
            body: $body,
            timeout: $this->timeout
        );

        if (!$response->isSuccessful()) {
            throw ApiException::fromResponse($response);
        }

        return $response;
    }

    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function getTimeout(): int
    {
        return $this->timeout;
    }

    public function getRequestId(): ?string
    {
        return $this->requestId;
    }

    public function getHttpClient(): HttpClientInterface
    {
        return $this->httpClient;
    }

    /**
     * Redact sensitive API key when object is inspected or dumped.
     *
     * @return array<string, mixed>
     */
    public function __debugInfo(): array
    {
        return [
            'apiKey' => '***[REDACTED]***',
            'baseUrl' => $this->baseUrl,
            'timeout' => $this->timeout,
            'requestId' => $this->requestId,
            'httpClient' => get_class($this->httpClient),
        ];
    }
}
