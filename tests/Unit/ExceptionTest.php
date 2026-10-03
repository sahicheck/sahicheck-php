<?php

declare(strict_types=1);

namespace SahiCheck\Tests\Unit;

use PHPUnit\Framework\TestCase;
use SahiCheck\Exception\ApiException;
use SahiCheck\Exception\AuthenticationException;
use SahiCheck\Exception\InsufficientCreditsException;
use SahiCheck\Exception\NetworkException;
use SahiCheck\Exception\RateLimitException;
use SahiCheck\Exception\ServerException;
use SahiCheck\Exception\ValidationException;
use SahiCheck\Http\HttpResponse;
use SahiCheck\SahiCheckClient;
use SahiCheck\Tests\Support\MockHttpClient;

class ExceptionTest extends TestCase
{
    public function testAuthenticationExceptionOn401(): void
    {
        $mockHttp = new MockHttpClient();
        $mockHttp->setDefaultResponse(new HttpResponse(
            statusCode: 401,
            headers: ['X-Request-Id' => 'req_err_401'],
            body: (string) json_encode([
                'success' => false,
                'error' => [
                    'code' => 'UNAUTHORIZED',
                    'message' => 'The provided API key is invalid or unrecognized.',
                ],
                'request_id' => 'req_err_401',
            ])
        ));

        $client = new SahiCheckClient('test_key', httpClient: $mockHttp);

        try {
            $client->phone()->validate('+919876543210');
            $this->fail('Expected AuthenticationException was not thrown.');
        } catch (AuthenticationException $e) {
            $this->assertSame(401, $e->getCode());
            $this->assertSame('The provided API key is invalid or unrecognized.', $e->getMessage());
            $this->assertSame('req_err_401', $e->getRequestId());
            $this->assertSame('UNAUTHORIZED', $e->getErrorCode());
            $this->assertInstanceOf(HttpResponse::class, $e->getResponse());
            $this->assertIsArray($e->getRaw());
            $this->assertStringNotContainsString('test_key', $e->getMessage());
        }
    }

    public function testInsufficientCreditsExceptionOn402(): void
    {
        $mockHttp = new MockHttpClient();
        $mockHttp->setDefaultResponse(new HttpResponse(
            statusCode: 402,
            headers: ['X-Request-Id' => 'req_err_402'],
            body: (string) json_encode([
                'success' => false,
                'error' => [
                    'code' => 'INSUFFICIENT_CREDITS',
                    'message' => 'Insufficient credit balance (0). This operation requires 1 credit(s).',
                ],
                'request_id' => 'req_err_402',
            ])
        ));

        $client = new SahiCheckClient('test_key', httpClient: $mockHttp);

        try {
            $client->email()->verify('test@example.com');
            $this->fail('Expected InsufficientCreditsException was not thrown.');
        } catch (InsufficientCreditsException $e) {
            $this->assertSame(402, $e->getCode());
            $this->assertSame('INSUFFICIENT_CREDITS', $e->getErrorCode());
            $this->assertSame('req_err_402', $e->getRequestId());
            $this->assertStringContainsString('Insufficient credit balance', $e->getMessage());
        }
    }

    public function testValidationExceptionOn422(): void
    {
        $mockHttp = new MockHttpClient();
        $mockHttp->setDefaultResponse(new HttpResponse(
            statusCode: 422,
            headers: ['X-Request-Id' => 'req_err_422'],
            body: (string) json_encode([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'The phone number must be at least 5 characters.',
                    'details' => [
                        'phone' => ['The phone number must be at least 5 characters.'],
                    ],
                ],
                'request_id' => 'req_err_422',
            ])
        ));

        $client = new SahiCheckClient('test_key', httpClient: $mockHttp);

        try {
            $client->phone()->validate('12');
            $this->fail('Expected ValidationException was not thrown.');
        } catch (ValidationException $e) {
            $this->assertSame(422, $e->getCode());
            $this->assertSame('VALIDATION_ERROR', $e->getErrorCode());
            $this->assertSame('req_err_422', $e->getRequestId());
            $this->assertSame('The phone number must be at least 5 characters.', $e->getMessage());
            $this->assertArrayHasKey('phone', $e->getDetails());
            $this->assertSame($e->getDetails(), $e->getErrors());
        }
    }

    public function testRateLimitExceptionOn429(): void
    {
        $mockHttp = new MockHttpClient();
        $mockHttp->setDefaultResponse(new HttpResponse(
            statusCode: 429,
            headers: [
                'Retry-After' => '30',
                'X-RateLimit-Limit' => '60',
                'X-RateLimit-Remaining' => '0',
                'X-Request-Id' => 'req_err_429',
            ],
            body: (string) json_encode([
                'success' => false,
                'error' => [
                    'code' => 'RATE_LIMIT_EXCEEDED',
                    'message' => 'Too many requests. Please try again later.',
                ],
                'request_id' => 'req_err_429',
            ])
        ));

        $client = new SahiCheckClient('test_key', httpClient: $mockHttp);

        try {
            $client->ip()->lookup('8.8.8.8');
            $this->fail('Expected RateLimitException was not thrown.');
        } catch (RateLimitException $e) {
            $this->assertSame(429, $e->getCode());
            $this->assertSame('RATE_LIMIT_EXCEEDED', $e->getErrorCode());
            $this->assertSame(30, $e->retryAfter());
            $this->assertSame(30, $e->getRetryAfter());
            $this->assertSame(60, $e->getRateLimit());
            $this->assertSame(0, $e->getRateLimitRemaining());
            $this->assertSame('req_err_429', $e->getRequestId());
        }
    }

    public function testServerExceptionOn500And502(): void
    {
        $mockHttp = new MockHttpClient();
        $mockHttp->queueResponse(new HttpResponse(
            statusCode: 500,
            headers: ['X-Request-Id' => 'req_err_500'],
            body: (string) json_encode([
                'success' => false,
                'error' => [
                    'code' => 'WALLET_ERROR',
                    'message' => 'Unable to finalize credit transaction.',
                ],
                'request_id' => 'req_err_500',
            ])
        ));
        $mockHttp->queueResponse(new HttpResponse(
            statusCode: 502,
            headers: ['X-Request-Id' => 'req_err_502'],
            body: (string) json_encode([
                'success' => false,
                'error' => [
                    'code' => 'PROVIDER_ERROR',
                    'message' => 'Verification provider temporarily unavailable. Please try again.',
                ],
                'request_id' => 'req_err_502',
            ])
        ));

        $client = new SahiCheckClient('test_key', httpClient: $mockHttp);

        // First: 500
        try {
            $client->phone()->validate('+919876543210');
            $this->fail('Expected ServerException for 500 was not thrown.');
        } catch (ServerException $e) {
            $this->assertSame(500, $e->getCode());
            $this->assertSame('WALLET_ERROR', $e->getErrorCode());
            $this->assertSame('req_err_500', $e->getRequestId());
        }

        // Second: 502
        try {
            $client->phone()->validate('+919876543210');
            $this->fail('Expected ServerException for 502 was not thrown.');
        } catch (ServerException $e) {
            $this->assertSame(502, $e->getCode());
            $this->assertSame('PROVIDER_ERROR', $e->getErrorCode());
            $this->assertSame('req_err_502', $e->getRequestId());
        }
    }

    public function testGenericApiExceptionForOtherStatuses(): void
    {
        $mockHttp = new MockHttpClient();
        $mockHttp->setDefaultResponse(new HttpResponse(
            statusCode: 403,
            headers: ['X-Request-Id' => 'req_err_403'],
            body: (string) json_encode([
                'success' => false,
                'error' => [
                    'code' => 'FORBIDDEN',
                    'message' => 'Access denied.',
                ],
                'request_id' => 'req_err_403',
            ])
        ));

        $client = new SahiCheckClient('test_key', httpClient: $mockHttp);

        try {
            $client->phone()->validate('+919876543210');
            $this->fail('Expected ApiException was not thrown.');
        } catch (ApiException $e) {
            $this->assertSame(403, $e->getCode());
            $this->assertSame('FORBIDDEN', $e->getErrorCode());
            $this->assertSame('req_err_403', $e->getRequestId());
            $this->assertSame('Access denied.', $e->getMessage());
        }
    }

    public function testNetworkExceptionOnConnectionFailure(): void
    {
        $mockHttp = new MockHttpClient();
        $mockHttp->queueResponse(new NetworkException('Connection timed out after 10 seconds.'));

        $client = new SahiCheckClient('test_key', httpClient: $mockHttp);

        $this->expectException(NetworkException::class);
        $this->expectExceptionMessage('Connection timed out after 10 seconds.');

        $client->phone()->validate('+919876543210');
    }
}
