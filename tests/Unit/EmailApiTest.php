<?php

declare(strict_types=1);

namespace SahiCheck\Tests\Unit;

use PHPUnit\Framework\TestCase;
use SahiCheck\Email\EmailVerificationResult;
use SahiCheck\Http\HttpResponse;
use SahiCheck\SahiCheckClient;
use SahiCheck\Tests\Support\MockHttpClient;

class EmailApiTest extends TestCase
{
    public function testEmailVerifySuccessful(): void
    {
        $mockHttp = new MockHttpClient();
        $responseJson = json_encode([
            'success' => true,
            'data' => [
                'valid' => true,
                'email' => 'alex@example.com',
                'user' => 'alex',
                'domain' => 'example.com',
                'syntax' => true,
                'mx' => true,
                'disposable' => false,
                'role' => false,
                'free' => false,
                'score' => 0.95,
            ],
            'credits_used' => 1,
            'request_id' => 'req_email_test_001',
        ]);

        $mockHttp->setDefaultResponse(new HttpResponse(200, ['X-Request-Id' => 'req_email_test_001'], (string) $responseJson));

        $client = new SahiCheckClient('test_key', httpClient: $mockHttp);
        $result = $client->email()->verify('alex@example.com');

        $this->assertInstanceOf(EmailVerificationResult::class, $result);
        $this->assertTrue($result->valid);
        $this->assertSame('alex@example.com', $result->email);
        $this->assertSame('alex', $result->user);
        $this->assertSame('example.com', $result->domain);
        $this->assertTrue($result->syntax);
        $this->assertTrue($result->mx);
        $this->assertFalse($result->disposable);
        $this->assertFalse($result->role);
        $this->assertFalse($result->free);
        $this->assertSame(0.95, $result->score);

        $this->assertSame(1, $result->creditsUsed);
        $this->assertSame(1, $result->credits_used);
        $this->assertSame('req_email_test_001', $result->requestId);
        $this->assertSame('req_email_test_001', $result->request_id);

        $lastRequest = $mockHttp->getLastRequest();
        $this->assertNotNull($lastRequest);
        $this->assertSame('POST', $lastRequest['method']);
        $this->assertSame('https://api.sahicheck.com/api/v1/email/verify', $lastRequest['url']);
        $this->assertSame(['email' => 'alex@example.com'], $lastRequest['body']);
    }
}
