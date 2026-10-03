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
    fwrite(STDERR, "Usage: SAHICHECK_API_KEY=your_key [SAHICHECK_BASE_URL=http://localhost:8000] php examples/email.php\n");
    exit(1);
}

$client = new SahiCheckClient(
    apiKey: $apiKey,
    baseUrl: $baseUrl
);

echo "Verifying email address (test@example.com) against {$baseUrl}...\n";

try {
    $result = $client->email()->verify(
        email: 'test@example.com'
    );

    echo "Status:        " . ($result->valid ? 'VALID' : 'INVALID') . "\n";
    echo "Email:         {$result->email}\n";
    echo "User:          {$result->user}\n";
    echo "Domain:        {$result->domain}\n";
    echo "Syntax Valid:  " . ($result->syntax ? 'Yes' : 'No') . "\n";
    echo "MX Records:    " . ($result->mx ? 'Yes' : 'No') . "\n";
    echo "Disposable:    " . ($result->disposable ? 'Yes' : 'No') . "\n";
    echo "Role Account:  " . ($result->role ? 'Yes' : 'No') . "\n";
    echo "Free Provider: " . ($result->free ? 'Yes' : 'No') . "\n";
    echo "Quality Score: " . ($result->score ?? 'N/A') . "\n";
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
