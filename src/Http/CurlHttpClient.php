<?php

declare(strict_types=1);

namespace SahiCheck\Http;

use JsonException;
use SahiCheck\Exception\NetworkException;

class CurlHttpClient implements HttpClientInterface
{
    /**
     * @param bool $verifySsl Whether to verify SSL certificates
     */
    public function __construct(
        protected readonly bool $verifySsl = true
    ) {}

    /**
     * {@inheritdoc}
     */
    public function send(
        string $method,
        string $url,
        array $headers = [],
        ?array $body = null,
        int $timeout = 10
    ): HttpResponse {
        if (!extension_loaded('curl')) {
            throw new NetworkException('The PHP cURL extension is required to perform HTTP requests.');
        }

        $ch = curl_init();
        if ($ch === false) {
            throw new NetworkException('Failed to initialize cURL handle.');
        }

        $method = strtoupper($method);
        $curlOptions = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_TIMEOUT => max(1, $timeout),
            CURLOPT_CONNECTTIMEOUT => min(5, max(1, $timeout)),
            CURLOPT_SSL_VERIFYPEER => $this->verifySsl,
            CURLOPT_SSL_VERIFYHOST => $this->verifySsl ? 2 : 0,
        ];

        if ($method === 'POST') {
            $curlOptions[CURLOPT_POST] = true;
        } elseif ($method !== 'GET') {
            $curlOptions[CURLOPT_CUSTOMREQUEST] = $method;
        }

        if ($body !== null) {
            try {
                $payload = json_encode($body, JSON_THROW_ON_ERROR);
            } catch (JsonException $e) {
                throw new NetworkException('Failed to JSON-encode request payload: ' . $e->getMessage(), 0, null, $e);
            }
            $curlOptions[CURLOPT_POSTFIELDS] = $payload;
        }

        $formattedHeaders = [];
        foreach ($headers as $key => $value) {
            $formattedHeaders[] = "{$key}: {$value}";
        }
        $curlOptions[CURLOPT_HTTPHEADER] = $formattedHeaders;

        curl_setopt_array($ch, $curlOptions);

        $response = curl_exec($ch);

        if ($response === false) {
            $errno = curl_errno($ch);
            $error = curl_error($ch);

            if ($errno === CURLE_OPERATION_TIMEDOUT) {
                throw new NetworkException(
                    message: sprintf('Request to SahiCheck API timed out after %d seconds.', $timeout),
                    code: $errno
                );
            }

            throw new NetworkException(
                message: 'Failed to connect to SahiCheck API: ' . $error,
                code: $errno
            );
        }

        $headerSize = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $statusCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);

        $headerContent = substr((string) $response, 0, $headerSize);
        $bodyContent = substr((string) $response, $headerSize);

        $parsedHeaders = $this->parseHeaders($headerContent);

        return new HttpResponse(
            statusCode: $statusCode,
            headers: $parsedHeaders,
            body: $bodyContent
        );
    }

    /**
     * @param string $headerContent
     * @return array<string, string>
     */
    protected function parseHeaders(string $headerContent): array
    {
        $headers = [];
        $lines = explode("\r\n", $headerContent);

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, 'HTTP/')) {
                continue;
            }

            $parts = explode(':', $line, 2);
            if (count($parts) === 2) {
                $headerName = trim($parts[0]);
                $headerValue = trim($parts[1]);
                $headers[$headerName] = $headerValue;
            }
        }

        return $headers;
    }
}
