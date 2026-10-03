<?php

declare(strict_types=1);

namespace SahiCheck\Tests\Unit;

use PHPUnit\Framework\TestCase;
use SahiCheck\Http\HttpResponse;
use SahiCheck\Ip\IpLookupResult;
use SahiCheck\SahiCheckClient;
use SahiCheck\Tests\Support\MockHttpClient;

class IpApiTest extends TestCase
{
    public function testIpLookupSuccessful(): void
    {
        $mockHttp = new MockHttpClient();
        $responseJson = json_encode([
            'success' => true,
            'data' => [
                'valid' => true,
                'ip' => '8.8.8.8',
                'country' => 'United States',
                'country_code' => 'US',
                'city' => 'Mountain View',
                'region' => 'California',
                'asn' => 'AS15169',
                'organization' => 'Google LLC',
                'timezone' => 'America/Los_Angeles',
                'vpn' => false,
                'proxy' => false,
                'tor' => false,
            ],
            'credits_used' => 1,
            'request_id' => 'req_ip_test_001',
        ]);

        $mockHttp->setDefaultResponse(new HttpResponse(200, ['X-Request-Id' => 'req_ip_test_001'], (string) $responseJson));

        $client = new SahiCheckClient('test_key', httpClient: $mockHttp);
        $result = $client->ip()->lookup('8.8.8.8');

        $this->assertInstanceOf(IpLookupResult::class, $result);
        $this->assertTrue($result->valid);
        $this->assertSame('8.8.8.8', $result->ip);
        $this->assertSame('United States', $result->country);
        $this->assertSame('US', $result->countryCode);
        $this->assertSame('US', $result->country_code);
        $this->assertSame('Mountain View', $result->city);
        $this->assertSame('California', $result->region);
        $this->assertSame('AS15169', $result->asn);
        $this->assertSame('Google LLC', $result->organization);
        $this->assertSame('America/Los_Angeles', $result->timezone);
        $this->assertFalse($result->vpn);
        $this->assertFalse($result->proxy);
        $this->assertFalse($result->tor);

        $this->assertSame(1, $result->creditsUsed);
        $this->assertSame(1, $result->credits_used);
        $this->assertSame('req_ip_test_001', $result->requestId);
        $this->assertSame('req_ip_test_001', $result->request_id);

        $lastRequest = $mockHttp->getLastRequest();
        $this->assertNotNull($lastRequest);
        $this->assertSame('GET', $lastRequest['method']);
        $this->assertSame('https://api.sahicheck.com/api/v1/ip/lookup?ip=8.8.8.8', $lastRequest['url']);
        $this->assertNull($lastRequest['body']);
    }
}
