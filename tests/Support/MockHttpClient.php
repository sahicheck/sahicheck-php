<?php

declare(strict_types=1);

namespace SahiCheck\Tests\Support;

use SahiCheck\Http\HttpClientInterface;
use SahiCheck\Http\HttpResponse;
use Throwable;

class MockHttpClient implements HttpClientInterface
{
    /**
     * @var list<array{
     *     method: string,
     *     url: string,
     *     headers: array<string, string>,
     *     body: array<string, mixed>|null,
     *     timeout: int
     * }>
     */
    public array $recordedRequests = [];

    /**
     * @var list<HttpResponse|Throwable>
     */
    protected array $queue = [];

    protected ?HttpResponse $defaultResponse = null;

    public function queueResponse(HttpResponse|Throwable $response): self
    {
        $this->queue[] = $response;
        return $this;
    }

    public function setDefaultResponse(HttpResponse $response): self
    {
        $this->defaultResponse = $response;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function send(
        string $method,
        string $url,
        array $headers = [],
        ?array $body = null,
        int $timeout = 10
    ): HttpResponse {
        $this->recordedRequests[] = [
            'method' => $method,
            'url' => $url,
            'headers' => $headers,
            'body' => $body,
            'timeout' => $timeout,
        ];

        if (!empty($this->queue)) {
            $item = array_shift($this->queue);
            if ($item instanceof Throwable) {
                throw $item;
            }
            return $item;
        }

        if ($this->defaultResponse !== null) {
            return $this->defaultResponse;
        }

        return new HttpResponse(200, ['Content-Type' => 'application/json'], '{"success":true,"data":[]}');
    }

    /**
     * @return array{
     *     method: string,
     *     url: string,
     *     headers: array<string, string>,
     *     body: array<string, mixed>|null,
     *     timeout: int
     * }|null
     */
    public function getLastRequest(): ?array
    {
        if (empty($this->recordedRequests)) {
            return null;
        }

        return $this->recordedRequests[array_key_last($this->recordedRequests)];
    }
}
