<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use SahiCheck\Exception\ApiException;
use SahiCheck\Exception\NetworkException;
use SahiCheck\Exception\SahiCheckException;
use SahiCheck\SahiCheckClient;

$apiKey = getenv('SAHICHECK_API_KEY');
$baseUrl = getenv('SAHICHECK_BASE_URL') ?: SahiCheckClient::DEFAULT_BASE_URL;

if (empty($apiKey)) {
    fwrite(STDERR, "Error: SAHICHECK_API_KEY environment variable is not set.\n");
    fwrite(STDERR, "Usage: SAHICHECK_API_KEY=your_key [SAHICHECK_BASE_URL=http://localhost:8000] php examples/ip.php\n");
    exit(1);
}

$client = new SahiCheckClient(
    apiKey: $apiKey,
    baseUrl: $baseUrl
);

echo "Looking up IP intelligence (8.8.8.8) against {$baseUrl}...\n";

try {
    $result = $client->ip()->lookup(
        ip: '8.8.8.8'
    );

    echo "Status:        " . ($result->valid ? 'VALID' : 'INVALID') . "\n";
    echo "IP:            {$result->ip}\n";
    echo "Country:       {$result->country} ({$result->countryCode})\n";
    echo "City/Region:   {$result->city}, {$result->region}\n";
    echo "ASN:           {$result->asn}\n";
    echo "Organization:  {$result->organization}\n";
    echo "Timezone:      {$result->timezone}\n";
    echo "VPN Detected:  " . ($result->vpn ? 'Yes' : 'No') . "\n";
    echo "Proxy:         " . ($result->proxy ? 'Yes' : 'No') . "\n";
    echo "Tor Exit Node: " . ($result->tor ? 'Yes' : 'No') . "\n";
    echo "Credits Used:  {$result->creditsUsed}\n";
    echo "Request ID:    {$result->requestId}\n";
} catch (ApiException $e) {
    fwrite(STDERR, "API Error ({$e->getCode()} [{$e->getErrorCode()}]): {$e->getMessage()}\n");
    if ($e->getRequestId() !== null) {
        fwrite(STDERR, "Request ID: {$e->getRequestId()}\n");
    }
    exit(1);
} catch (NetworkException $e) {
    fwrite(STDERR, "Network Error: {$e->getMessage()}\n");
    exit(1);
} catch (SahiCheckException $e) {
    fwrite(STDERR, "SahiCheck Error: {$e->getMessage()}\n");
    exit(1);
}
