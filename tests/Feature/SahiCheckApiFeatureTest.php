<?php

declare(strict_types=1);

namespace SahiCheck\Tests\Feature;

use PHPUnit\Framework\TestCase;
use SahiCheck\Http\HttpResponse;
use SahiCheck\Response\EmailVerificationResult as ResponseEmailResult;
use SahiCheck\Response\IpLookupResult as ResponseIpResult;
use SahiCheck\Response\PhoneVerificationResult as ResponsePhoneResult;
use SahiCheck\SahiCheckClient;
use SahiCheck\Tests\Support\MockHttpClient;

class SahiCheckApiFeatureTest extends TestCase
{
    public function testCompleteVerificationWorkflow(): void
    {
        $mockHttp = new MockHttpClient();

        // 1. Phone verification response
        $mockHttp->queueResponse(new HttpResponse(
            statusCode: 200,
            headers: ['X-Request-Id' => 'req_phone_flow_1'],
            body: (string) json_encode([
                'success' => true,
                'data' => [
                    'valid' => true,
                    'phone' => '+919876543210',
                    'country' => 'IN',
                    'calling_code' => '+91',
                    'national_format' => '098765 43210',
                    'international_format' => '+91 98765 43210',
                    'type' => 'mobile',
                    'carrier' => 'Airtel',
                ],
                'credits_used' => 1,
                'request_id' => 'req_phone_flow_1',
            ])
        ));

        // 2. Email verification response
        $mockHttp->queueResponse(new HttpResponse(
            statusCode: 200,
            headers: ['X-Request-Id' => 'req_email_flow_2'],
            body: (string) json_encode([
                'success' => true,
                'data' => [
                    'valid' => true,
                    'email' => 'contact@sahicheck.com',
                    'user' => 'contact',
                    'domain' => 'sahicheck.com',
                    'syntax' => true,
                    'mx' => true,
                    'disposable' => false,
                    'role' => true,
                    'free' => false,
                    'score' => 0.9,
                ],
                'credits_used' => 1,
                'request_id' => 'req_email_flow_2',
            ])
        ));

        // 3. IP lookup response
        $mockHttp->queueResponse(new HttpResponse(
            statusCode: 200,
            headers: ['X-Request-Id' => 'req_ip_flow_3'],
            body: (string) json_encode([
                'success' => true,
                'data' => [
                    'valid' => true,
                    'ip' => '1.1.1.1',
                    'country' => 'Australia',
                    'country_code' => 'AU',
                    'city' => 'Sydney',
                    'region' => 'New South Wales',
                    'asn' => 'AS13335',
                    'organization' => 'Cloudflare, Inc.',
                    'timezone' => 'Australia/Sydney',
                    'vpn' => false,
                    'proxy' => false,
                    'tor' => false,
                ],
                'credits_used' => 1,
                'request_id' => 'req_ip_flow_3',
            ])
        ));

        $client = new SahiCheckClient('test_live_key', httpClient: $mockHttp);

        // Execute phone verification
        $phoneResult = $client->phone()->validate('+919876543210', 'IN');
        $this->assertInstanceOf(ResponsePhoneResult::class, $phoneResult);
        $this->assertTrue($phoneResult->valid);
        $this->assertSame('+919876543210', $phoneResult->phone);
        $this->assertSame('req_phone_flow_1', $phoneResult->requestId);
        $this->assertSame(1, $phoneResult->creditsUsed);

        // Execute email verification
        $emailResult = $client->email()->verify('contact@sahicheck.com');
        $this->assertInstanceOf(ResponseEmailResult::class, $emailResult);
        $this->assertTrue($emailResult->valid);
        $this->assertSame('contact@sahicheck.com', $emailResult->email);
        $this->assertTrue($emailResult->role);
        $this->assertSame('req_email_flow_2', $emailResult->requestId);

        // Execute IP lookup
        $ipResult = $client->ip()->lookup('1.1.1.1');
        $this->assertInstanceOf(ResponseIpResult::class, $ipResult);
        $this->assertTrue($ipResult->valid);
        $this->assertSame('1.1.1.1', $ipResult->ip);
        $this->assertSame('AU', $ipResult->countryCode);
        $this->assertSame('Cloudflare, Inc.', $ipResult->organization);
        $this->assertSame('req_ip_flow_3', $ipResult->requestId);

        // Check all 3 requests recorded properly
        $this->assertCount(3, $mockHttp->recordedRequests);
        $this->assertSame('POST', $mockHttp->recordedRequests[0]['method']);
        $this->assertSame('POST', $mockHttp->recordedRequests[1]['method']);
        $this->assertSame('GET', $mockHttp->recordedRequests[2]['method']);
    }
}
