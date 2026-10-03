<?php

declare(strict_types=1);

namespace SahiCheck\Tests\Unit;

use PHPUnit\Framework\TestCase;
use SahiCheck\Http\HttpResponse;

class HttpResponseTest extends TestCase
{
    public function testHeaderLookupIsCaseInsensitive(): void
    {
        $response = new HttpResponse(
            statusCode: 200,
            headers: [
                'X-Request-Id' => 'req_test_abc',
                'Content-Type' => 'application/json',
            ],
            body: '{"success":true}'
        );

        $this->assertTrue($response->isSuccessful());
        $this->assertSame('req_test_abc', $response->getHeader('x-request-id'));
        $this->assertSame('req_test_abc', $response->getHeader('X-REQUEST-ID'));
        $this->assertSame('req_test_abc', $response->getRequestId());
        $this->assertSame('application/json', $response->getHeader('content-type'));
        $this->assertNull($response->getHeader('non-existent'));
    }

    public function testRequestIdFallsBackToJsonBody(): void
    {
        $response = new HttpResponse(
            statusCode: 200,
            headers: [],
            body: '{"success":true,"request_id":"req_from_body"}'
        );

        $this->assertSame('req_from_body', $response->getRequestId());
    }

    public function testJsonReturnsEmptyArrayOnMalformedBody(): void
    {
        $response = new HttpResponse(
            statusCode: 500,
            headers: [],
            body: 'non-json content'
        );

        $this->assertSame([], $response->json());
        $this->assertFalse($response->isSuccessful());
    }
}
