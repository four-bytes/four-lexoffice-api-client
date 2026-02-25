<?php

declare(strict_types=1);

namespace Four\LexofficeApiClient;

use Four\LexofficeApiClient\Exception\LexofficeException;
use Four\LexofficeApiClient\Exception\RateLimitException;

class LexofficeClient
{
    private const BASE_URL = 'https://api.lexware.io/v1/';
    private const USER_AGENT = 'four-lexoffice-api-client/0.1';
    private const MAX_RETRIES = 3;
    private const RATE_LIMIT_WAIT_MS = 1000;
    private const MIN_REQUEST_INTERVAL_MS = 1000;

    private static ?float $lastRequest = null;

    public function __construct(
        private readonly string $apiKey,
    ) {
    }

    /**
     * Sendet einen HTTP-Request an die lexoffice API.
     *
     * @return array<mixed>
     * @throws LexofficeException
     * @throws RateLimitException
     */
    public function sendRequest(string $endpoint, mixed $body = null, string $method = 'GET'): array
    {
        $this->throttle();

        $url = self::BASE_URL . ltrim($endpoint, '/');
        $attempt = 0;

        while ($attempt < self::MAX_RETRIES) {
            $attempt++;
            [$statusCode, $responseBody] = $this->executeCurl($url, $method, $body);

            self::$lastRequest = microtime(true) * 1000;

            if ($statusCode === 429) {
                if ($attempt >= self::MAX_RETRIES) {
                    throw new RateLimitException(
                        message: 'Rate limit exceeded after ' . self::MAX_RETRIES . ' retries',
                        responseBody: $responseBody,
                    );
                }
                usleep(self::RATE_LIMIT_WAIT_MS * 1000);
                continue;
            }

            if ($statusCode < 200 || $statusCode >= 300) {
                throw new LexofficeException(
                    message: sprintf('lexoffice API error %d for %s %s', $statusCode, $method, $endpoint),
                    httpStatusCode: $statusCode,
                    responseBody: $responseBody,
                );
            }

            if (empty($responseBody)) {
                return [];
            }

            $decoded = json_decode($responseBody, true);
            if (!is_array($decoded)) {
                throw new LexofficeException(
                    message: 'Invalid JSON response from lexoffice API',
                    httpStatusCode: $statusCode,
                    responseBody: $responseBody,
                );
            }

            return $decoded;
        }

        throw new LexofficeException(
            message: 'Request failed after ' . self::MAX_RETRIES . ' attempts',
            httpStatusCode: 0,
            responseBody: '',
        );
    }

    /**
     * Lädt ein PDF von der API herunter und gibt die rohen Bytes zurück.
     *
     * @throws LexofficeException
     * @throws RateLimitException
     */
    public function downloadFile(string $endpoint): string
    {
        $this->throttle();

        $url = self::BASE_URL . ltrim($endpoint, '/');
        $attempt = 0;

        while ($attempt < self::MAX_RETRIES) {
            $attempt++;

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL            => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER     => [
                    'Authorization: Bearer ' . $this->apiKey,
                    'Accept: application/pdf',
                    'User-Agent: ' . self::USER_AGENT,
                ],
                CURLOPT_TIMEOUT        => 60,
            ]);

            $responseBody = (string) curl_exec($ch);
            $statusCode   = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if (curl_errno($ch)) {
                $error = curl_error($ch);
                curl_close($ch);
                throw new LexofficeException(
                    message: 'cURL-Fehler beim PDF-Download: ' . $error,
                    httpStatusCode: 0,
                );
            }

            curl_close($ch);

            self::$lastRequest = microtime(true) * 1000;

            if ($statusCode === 429) {
                if ($attempt >= self::MAX_RETRIES) {
                    throw new RateLimitException(
                        message: 'Rate limit exceeded after ' . self::MAX_RETRIES . ' retries',
                        responseBody: $responseBody,
                    );
                }
                usleep(self::RATE_LIMIT_WAIT_MS * 1000);
                continue;
            }

            if ($statusCode < 200 || $statusCode >= 300) {
                throw new LexofficeException(
                    message: sprintf('lexoffice API error %d für PDF-Download: %s', $statusCode, $endpoint),
                    httpStatusCode: $statusCode,
                    responseBody: $responseBody,
                );
            }

            return $responseBody;
        }

        throw new LexofficeException(
            message: 'PDF-Download failed after ' . self::MAX_RETRIES . ' attempts',
            httpStatusCode: 0,
            responseBody: '',
        );
    }

    /**
     * Lädt eine Datei per multipart/form-data hoch.
     *
     * @return array<mixed>
     * @throws LexofficeException
     * @throws RateLimitException
     */
    public function uploadFile(string $endpoint, string $filePath, string $type = 'voucher'): array
    {
        if (!file_exists($filePath)) {
            throw new LexofficeException(
                message: sprintf('Datei nicht gefunden: %s', $filePath),
                httpStatusCode: 0,
            );
        }

        $this->throttle();

        $url = self::BASE_URL . ltrim($endpoint, '/');
        $attempt = 0;

        while ($attempt < self::MAX_RETRIES) {
            $attempt++;

            $postFields = [
                'file' => new \CURLFile($filePath, 'application/pdf', basename($filePath)),
                'type' => $type,
            ];

            [$statusCode, $responseBody] = $this->executeCurlMultipart($url, $postFields);

            self::$lastRequest = microtime(true) * 1000;

            if ($statusCode === 429) {
                if ($attempt >= self::MAX_RETRIES) {
                    throw new RateLimitException(
                        message: 'Rate limit exceeded after ' . self::MAX_RETRIES . ' retries',
                        responseBody: $responseBody,
                    );
                }
                usleep(self::RATE_LIMIT_WAIT_MS * 1000);
                continue;
            }

            if ($statusCode < 200 || $statusCode >= 300) {
                throw new LexofficeException(
                    message: sprintf('lexoffice API error %d beim Datei-Upload: %s', $statusCode, $endpoint),
                    httpStatusCode: $statusCode,
                    responseBody: $responseBody,
                );
            }

            if (empty($responseBody)) {
                return [];
            }

            $decoded = json_decode($responseBody, true);
            if (!is_array($decoded)) {
                throw new LexofficeException(
                    message: 'Invalid JSON response from lexoffice API',
                    httpStatusCode: $statusCode,
                    responseBody: $responseBody,
                );
            }

            return $decoded;
        }

        throw new LexofficeException(
            message: 'Upload failed after ' . self::MAX_RETRIES . ' attempts',
            httpStatusCode: 0,
            responseBody: '',
        );
    }

    /**
     * Rate-Limiting: min. 1000ms zwischen Requests einhalten.
     */
    private function throttle(): void
    {
        if (self::$lastRequest === null) {
            return;
        }

        $elapsed = (microtime(true) * 1000) - self::$lastRequest;
        if ($elapsed < self::MIN_REQUEST_INTERVAL_MS) {
            $waitUs = (int) ((self::MIN_REQUEST_INTERVAL_MS - $elapsed) * 1000);
            usleep($waitUs);
        }
    }

    /**
     * Führt einen cURL-Request aus und gibt [statusCode, body] zurück.
     *
     * @param array<mixed>|null $body
     * @return array{int, string}
     */
    private function executeCurl(string $url, string $method, mixed $body): array
    {
        $ch = curl_init();

        $headers = [
            'Authorization: Bearer ' . $this->apiKey,
            'Accept: application/json',
            'User-Agent: ' . self::USER_AGENT,
        ];

        $options = [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_TIMEOUT        => 30,
        ];

        if ($method === 'POST' || $method === 'PUT' || $method === 'PATCH') {
            $jsonBody = $body !== null ? json_encode($body) : '{}';
            $options[CURLOPT_POSTFIELDS] = $jsonBody;
            $options[CURLOPT_CUSTOMREQUEST] = $method;
            $headers[] = 'Content-Type: application/json';
            $options[CURLOPT_HTTPHEADER] = $headers;
        } elseif ($method !== 'GET') {
            $options[CURLOPT_CUSTOMREQUEST] = $method;
        }

        curl_setopt_array($ch, $options);

        $responseBody = (string) curl_exec($ch);
        $statusCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new LexofficeException(
                message: 'cURL-Fehler: ' . $error,
                httpStatusCode: 0,
            );
        }

        curl_close($ch);

        return [$statusCode, $responseBody];
    }

    /**
     * Führt einen multipart/form-data Upload per cURL aus.
     *
     * @param array<string, mixed> $postFields
     * @return array{int, string}
     */
    private function executeCurlMultipart(string $url, array $postFields): array
    {
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $postFields,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $this->apiKey,
                'Accept: application/json',
                'User-Agent: ' . self::USER_AGENT,
            ],
            CURLOPT_TIMEOUT        => 60,
        ]);

        $responseBody = (string) curl_exec($ch);
        $statusCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new LexofficeException(
                message: 'cURL-Fehler beim Upload: ' . $error,
                httpStatusCode: 0,
            );
        }

        curl_close($ch);

        return [$statusCode, $responseBody];
    }
}
