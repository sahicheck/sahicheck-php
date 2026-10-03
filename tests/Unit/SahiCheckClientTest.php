<?php

declare(strict_types=1);

namespace SahiCheck\Tests\Unit;

use PHPUnit\Framework\TestCase;
use SahiCheck\Email\EmailApi;
use SahiCheck\Http\HttpResponse;
use SahiCheck\Ip\IpApi;
use SahiCheck\Phone\PhoneApi;
use SahiCheck\SahiCheckClient;
use SahiCheck\Tests\Support\MockHttpClient;

class SahiCheckClientTest extends TestCase
{
    public function testClientInitializesWithDefaults(): void
    {
        $client = new SahiCheckClient('test_api_key_123');

        $this->assertSame('test_api_key_123', $client->getApiKey());
        $this->assertSame('https://api.sahicheck.com', $client->getBaseUrl());
        $this->assertSame(10, $client->getTimeout());
        $this->assertNull($client->getRequestId());
    }

    public function testBaseUrlNormalizesTrailingSlashes(): void
    {
        $client = new SahiCheckClient(
            apiKey: 'test_key',
            baseUrl: 'https://staging.sahicheck.com///'
        );

        $this->assertSame('https://staging.sahicheck.com', $client->getBaseUrl());
    }

    public function testCustomTimeoutAndBaseUrlConfiguration(): void
    {
        $client = new SahiCheckClient(
            apiKey: 'test_key',
            baseUrl: 'https://api.example.com',
            timeout: 25
        );

        $this->assertSame('https://api.example.com', $client->getBaseUrl());
        $this->assertSame(25, $client->getTimeout());

        $cloned = $client->withTimeout(5)->withBaseUrl('https://custom.api.com/');
        $this->assertSame(5, $cloned->getTimeout());
        $this->assertSame('https://custom.api.com', $cloned->getBaseUrl());
        // Original remains untouched (immutability)
        $this->assertSame(25, $client->getTimeout());
    }

    public function testWithRequestIdReturnsNewInstance(): void
    {
        $client = new SahiCheckClient('test_key');
        $this->assertNull($client->getRequestId());

        $withRequestId = $client->withRequestId('custom_req_999');
        $this->assertSame('custom_req_999', $withRequestId->getRequestId());
        $this->assertNull($client->getRequestId());
    }

    public function testEndpointAccessorsReturnExpectedInstances(): void
    {
        $client = new SahiCheckClient('test_key');

        $this->assertInstanceOf(PhoneApi::class, $client->phone());
        $this->assertInstanceOf(EmailApi::class, $client->email());
        $this->assertInstanceOf(IpApi::class, $client->ip());

        // Repeated calls return same cached instance
        $this->assertSame($client->phone(), $client->phone());
        $this->assertSame($client->email(), $client->email());
        $this->assertSame($client->ip(), $client->ip());
    }

    public function testSendRequestSendsExpectedHeadersAndUrl(): void
    {
        $mockHttp = new MockHttpClient();
        $mockHttp->setDefaultResponse(new HttpResponse(200, ['Content-Type' => 'application/json'], '{"success":true}'));

        $client = new SahiCheckClient(
            apiKey: 'secret_live_key',
            baseUrl: 'https://api.sahicheck.com',
            timeout: 15,
            httpClient: $mockHttp,
            requestId: 'custom_client_id'
        );

        $client->sendRequest(
            method: 'POST',
            path: '/api/v1/test',
            query: ['filter' => 'active'],
            body: ['sample' => 'data']
        );

        $lastRequest = $mockHttp->getLastRequest();
        $this->assertNotNull($lastRequest);
        $this->assertSame('POST', $lastRequest['method']);
        $this->assertSame('https://api.sahicheck.com/api/v1/test?filter=active', $lastRequest['url']);
        $this->assertSame(15, $lastRequest['timeout']);
        $this->assertSame(['sample' => 'data'], $lastRequest['body']);

        $headers = $lastRequest['headers'];
        $this->assertSame('secret_live_key', $headers['X-API-Key']);
        $this->assertSame('application/json', $headers['Accept']);
        $this->assertSame('application/json', $headers['Content-Type']);
        $this->assertSame('custom_client_id', $headers['X-Request-Id']);
    }

    public function testDebugInfoRedactsApiKey(): void
    {
        $client = new SahiCheckClient('super_secret_key_12345');
        $debugInfo = $client->__debugInfo();

        $this->assertArrayHasKey('apiKey', $debugInfo);
        $this->assertStringNotContainsString('super_secret_key_12345', $debugInfo['apiKey']);
        $this->assertSame('***[REDACTED]***', $debugInfo['apiKey']);

        $dump = print_r($client, true);
        $this->assertStringNotContainsString('super_secret_key_12345', $dump);
    }
}
