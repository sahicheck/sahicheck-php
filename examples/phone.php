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
    fwrite(STDERR, "Usage: SAHICHECK_API_KEY=your_key [SAHICHECK_BASE_URL=http://localhost:8000] php examples/phone.php\n");
    exit(1);
}

$client = new SahiCheckClient(
    apiKey: $apiKey,
    baseUrl: $baseUrl
);

echo "Validating phone number (+919876543210, IN) against {$baseUrl}...\n";

try {
    $result = $client->phone()->validate(
        phone: '+919876543210',
        countryCode: 'IN'
    );

    echo "Status:        " . ($result->valid ? 'VALID' : 'INVALID') . "\n";
    echo "Phone:         {$result->phone}\n";
    echo "Country:       {$result->country}\n";
    echo "Calling Code:  {$result->callingCode}\n";
    echo "National:      {$result->nationalFormat}\n";
    echo "International: {$result->internationalFormat}\n";
    echo "Type:          {$result->type}\n";
    echo "Carrier:       {$result->carrier}\n";
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
