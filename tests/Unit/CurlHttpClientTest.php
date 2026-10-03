<?php

declare(strict_types=1);

namespace SahiCheck\Tests\Unit;

use PHPUnit\Framework\TestCase;
use SahiCheck\Exception\NetworkException;
use SahiCheck\Http\CurlHttpClient;

class CurlHttpClientTest extends TestCase
{
    public function testUnreachableHostThrowsNetworkException(): void
    {
        $client = new CurlHttpClient(verifySsl: false);

        $this->expectException(NetworkException::class);
        // Attempting to connect to a reserved non-routable test address with very low timeout
        $client->send(
            method: 'GET',
            url: 'http://192.0.2.1:1',
            timeout: 1
        );
    }
}
