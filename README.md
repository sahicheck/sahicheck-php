# SahiCheck PHP SDK

The official PHP SDK for the [SahiCheck](https://sahicheck.com) Verification API.

Perform phone number validation, email deliverability verification, and IP intelligence lookups through a clean, modern, strictly typed PHP interface.

---

## Requirements

* **PHP:** >= 8.2
* **Extensions:** `ext-curl`, `ext-json`

---

## Installation

Install the package via Composer:

```bash
composer require sahicheck/sahicheck-php
```

---

## Quickstart & Authentication

Instantiate `SahiCheckClient` with your API key. Obtain your API key from your SahiCheck dashboard.

```php
use SahiCheck\SahiCheckClient;

$client = new SahiCheckClient(
    apiKey: getenv('SAHICHECK_API_KEY')
);
```

### Custom Base URL & Timeout

Override the default base URL (`https://api.sahicheck.com`) or HTTP timeout (default: 10 seconds):

```php
$client = new SahiCheckClient(
    apiKey: getenv('SAHICHECK_API_KEY'),
    baseUrl: 'https://api.sahicheck.com', // or 'http://localhost:8000' for local development
    timeout: 15
);
```

---

## Features & Usage

### 1. Phone Number Validation

Validate national and international phone numbers, detect formatting, line types (mobile, landline, voip), and carrier information:

```php
$result = $client->phone()->validate(
    phone: '+919876543210',
    countryCode: 'IN' // Optional 2-letter ISO country code
);

if ($result->valid) {
    echo "Phone:         {$result->phone}\n";
    echo "Country:       {$result->country}\n";
    echo "Calling Code:  {$result->callingCode}\n";
    echo "National:      {$result->nationalFormat}\n";
    echo "International: {$result->internationalFormat}\n";
    echo "Type:          {$result->type}\n";     // e.g. mobile, fixed_line
    echo "Carrier:       {$result->carrier}\n";  // e.g. Airtel
} else {
    echo "Invalid phone number.\n";
}

echo "Credits used: {$result->creditsUsed}\n";
echo "Request ID:   {$result->requestId}\n";
```

### 2. Email Address Verification

Verify email deliverability, MX records, disposable and role-based mailboxes, and calculate risk scores:

```php
$result = $client->email()->verify(
    email: 'user@example.com'
);

echo "Valid:       " . ($result->valid ? 'Yes' : 'No') . "\n";
echo "User:        {$result->user}\n";
echo "Domain:      {$result->domain}\n";
echo "Syntax:      " . ($result->syntax ? 'Valid' : 'Invalid') . "\n";
echo "MX Records:  " . ($result->mx ? 'Found' : 'Missing') . "\n";
echo "Disposable:  " . ($result->disposable ? 'Yes' : 'No') . "\n";
echo "Role-based:  " . ($result->role ? 'Yes' : 'No') . "\n";
echo "Free email:  " . ($result->free ? 'Yes' : 'No') . "\n";
echo "Score:       {$result->score}\n";
```

### 3. IP Intelligence Lookup

Look up geolocation, ASN, organization, and detect VPN, proxy, or Tor exit nodes:

```php
$result = $client->ip()->lookup(
    ip: '8.8.8.8'
);

echo "IP:           {$result->ip}\n";
echo "Country:      {$result->country} ({$result->countryCode})\n";
echo "City:         {$result->city}\n";
echo "Region:       {$result->region}\n";
echo "ASN:          {$result->asn}\n";
echo "Organization: {$result->organization}\n";
echo "Timezone:     {$result->timezone}\n";
echo "VPN:          " . ($result->vpn ? 'Detected' : 'No') . "\n";
echo "Proxy:        " . ($result->proxy ? 'Detected' : 'No') . "\n";
echo "Tor:          " . ($result->tor ? 'Detected' : 'No') . "\n";
```

### Accessing Raw Response Data

Access the complete JSON payload returned by the API via `raw()`:

```php
$rawArray = $result->raw();
```

---

## Error Handling

The SDK throws descriptive, typed exceptions for all error scenarios:

```php
use SahiCheck\SahiCheckClient;
use SahiCheck\Exception\AuthenticationException;
use SahiCheck\Exception\InsufficientCreditsException;
use SahiCheck\Exception\ValidationException;
use SahiCheck\Exception\RateLimitException;
use SahiCheck\Exception\ServerException;
use SahiCheck\Exception\NetworkException;
use SahiCheck\Exception\ApiException;
use SahiCheck\Exception\SahiCheckException;

try {
    $result = $client->phone()->validate('+919876543210', 'IN');
} catch (AuthenticationException $e) {
    // 401 Unauthorized: Invalid or revoked API key
    echo "Authentication failed: {$e->getMessage()}\n";
    echo "Error code: {$e->getErrorCode()}\n";
} catch (InsufficientCreditsException $e) {
    // 402 Payment Required: Not enough credits
    echo "Insufficient credits: {$e->getMessage()}\n";
} catch (ValidationException $e) {
    // 422 Unprocessable Content: Invalid input data
    echo "Validation error: {$e->getMessage()}\n";
    print_r($e->getErrors()); // Detailed validation errors
} catch (RateLimitException $e) {
    // 429 Too Many Requests: Rate limit exceeded
    echo "Rate limit exceeded. Retry after: {$e->retryAfter()} seconds\n";
} catch (ServerException $e) {
    // 500 / 502: Internal server error or upstream provider error
    echo "Server error ({$e->getCode()}): {$e->getMessage()}\n";
} catch (NetworkException $e) {
    // Connection failure or timeout
    echo "Network error: {$e->getMessage()}\n";
} catch (ApiException $e) {
    // Any other API response error
    echo "API error ({$e->getCode()}): {$e->getMessage()}\n";
} catch (SahiCheckException $e) {
    // Generic base exception
    echo "SDK error: {$e->getMessage()}\n";
}
```

---

## Request IDs & Tracking

Every SahiCheck API response includes a unique `request_id` for tracking and support:

```php
// Retrieve request ID from successful results:
echo $result->requestId;

// Retrieve request ID from exceptions:
try {
    $client->phone()->validate('+919876543210');
} catch (SahiCheckException $e) {
    echo "Failed request ID: " . $e->getRequestId();
}
```

### Client-Provided Request ID

Send a custom correlation ID with your requests via `withRequestId()`:

```php
$result = $client
    ->withRequestId('order_req_abc123')
    ->phone()
    ->validate('+919876543210');

echo $result->requestId; // 'order_req_abc123'
```

---

## Testing

Run the automated test suite with PHPUnit:

```bash
composer test
```

All automated tests use a mock HTTP transport and never make live API calls.

---

## License

This SDK is open-source software licensed under the [MIT license](LICENSE).
