<?php

declare(strict_types=1);

namespace SahiCheck\Tests\Unit;

use PHPUnit\Framework\TestCase;
use SahiCheck\Http\HttpResponse;
use SahiCheck\Phone\PhoneVerificationResult;
use SahiCheck\SahiCheckClient;
use SahiCheck\Tests\Support\MockHttpClient;

class PhoneApiTest extends TestCase
{
    public function testPhoneValidateSuccessful(): void
    {
        $mockHttp = new MockHttpClient();
        $responseJson = json_encode([
            'success' => true,
            'data' => [
                'valid' => true,
                'phone' => '+919876543210',
                'country' => 'IN',
                'calling_code' => '+91',
                'national_format' => '098765 43210',
                'international_format' => '+91 98765 43210',
                'type' => 'mobile',
                'carrier' => 'Airtel India',
            ],
            'credits_used' => 1,
            'request_id' => 'req_phone_test_001',
        ]);

        $mockHttp->setDefaultResponse(new HttpResponse(200, ['X-Request-Id' => 'req_phone_test_001'], (string) $responseJson));

        $client = new SahiCheckClient(
            apiKey: 'test_key',
            httpClient: $mockHttp
        );

        $result = $client->phone()->validate(
            phone: '+919876543210',
            countryCode: 'in'
        );

        $this->assertInstanceOf(PhoneVerificationResult::class, $result);
        $this->assertTrue($result->valid);
        $this->assertSame('+919876543210', $result->phone);
        $this->assertSame('IN', $result->country);
        $this->assertSame('+91', $result->callingCode);
        $this->assertSame('+91', $result->calling_code);
        $this->assertSame('098765 43210', $result->nationalFormat);
        $this->assertSame('098765 43210', $result->national_format);
        $this->assertSame('+91 98765 43210', $result->internationalFormat);
        $this->assertSame('+91 98765 43210', $result->international_format);
        $this->assertSame('mobile', $result->type);
        $this->assertSame('Airtel India', $result->carrier);

        $this->assertSame(1, $result->creditsUsed);
        $this->assertSame(1, $result->credits_used);
        $this->assertSame('req_phone_test_001', $result->requestId);
        $this->assertSame('req_phone_test_001', $result->request_id);
        $this->assertTrue($result->isSuccess());

        $this->assertIsArray($result->raw());
        $this->assertSame('req_phone_test_001', $result->raw()['request_id']);

        $lastRequest = $mockHttp->getLastRequest();
        $this->assertNotNull($lastRequest);
        $this->assertSame('POST', $lastRequest['method']);
        $this->assertSame('https://api.sahicheck.com/api/v1/phone/validate', $lastRequest['url']);
        $this->assertSame([
            'phone' => '+919876543210',
            'country_code' => 'IN',
        ], $lastRequest['body']);
    }

    public function testPhoneValidateWithoutCountryCodeOmitsCountryCodeFromPayload(): void
    {
        $mockHttp = new MockHttpClient();
        $responseJson = json_encode([
            'success' => true,
            'data' => [
                'valid' => false,
                'phone' => '+12345',
                'country' => null,
                'calling_code' => null,
                'national_format' => null,
                'international_format' => null,
                'type' => null,
                'carrier' => null,
            ],
            'credits_used' => 1,
            'request_id' => 'req_phone_invalid_002',
        ]);

        $mockHttp->setDefaultResponse(new HttpResponse(200, [], (string) $responseJson));

        $client = new SahiCheckClient('test_key', httpClient: $mockHttp);
        $result = $client->phone()->validate('+12345');

        $this->assertFalse($result->valid);
        $this->assertSame('+12345', $result->phone);
        $this->assertNull($result->country);

        $lastRequest = $mockHttp->getLastRequest();
        $this->assertNotNull($lastRequest);
        $this->assertSame(['phone' => '+12345'], $lastRequest['body']);
    }
}
