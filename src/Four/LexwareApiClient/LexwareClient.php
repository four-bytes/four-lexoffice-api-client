<?php

declare(strict_types=1);

namespace Four\LexwareApiClient;

use Four\Http\Configuration\ClientConfig;
use Four\Http\Configuration\RetryConfig;
use Four\Http\Factory\HttpClientFactory;
use Four\LexwareApiClient\DTO\DownloadedFile;
use Four\LexwareApiClient\Exception\LexwareException;
use Four\LexwareApiClient\Exception\RateLimitException;
use Four\RateLimit\RateLimitConfiguration;
use Four\RateLimit\RateLimiterFactory;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

class LexwareClient
{
    private const BASE_URL = 'https://api.lexware.io/v1/';
    private const APP_URL = 'https://app.lexware.de';
    private const USER_AGENT = 'four-lexware-api-client/1.2.0';

    public function __construct(
        private readonly string $apiKey,
        private readonly ClientInterface $httpClient,
        private readonly RequestFactoryInterface $requestFactory,
        private readonly StreamFactoryInterface $streamFactory,
    ) {}

    public static function create(string $apiKey): self
    {
        $rateLimiterConfig = new RateLimitConfiguration(
            algorithm: RateLimitConfiguration::ALGORITHM_FIXED_WINDOW,
            ratePerSecond: 1.0,
            burstCapacity: 1,
        );
        $rateLimiter = (new RateLimiterFactory())->create($rateLimiterConfig);

        $retryConfig = new RetryConfig(
            maxAttempts: 3,
            retryableStatusCodes: [429],
        );

        $config = new ClientConfig(
            baseUri: self::BASE_URL,
            defaultHeaders: [
                'Authorization' => 'Bearer ' . $apiKey,
                'Accept'        => 'application/json',
                'User-Agent'    => self::USER_AGENT,
            ],
            middleware: ['retry', 'rate_limiting'],
            rateLimiter: $rateLimiter,
            retryConfig: $retryConfig,
            timeout: 30.0,
        );

        $psrClient = (new HttpClientFactory())->create($config);
        $psr17 = new Psr17Factory();

        return new self($apiKey, $psrClient, $psr17, $psr17);
    }

    /**
     * Builds a deeplink to a resource in the lexware web app.
     *
     * @param string $resource Resource type (e.g. "contacts", "invoices", "vouchers").
     * @param string $id       Resource id.
     */
    public function deeplinkUrl(string $resource, string $id): string
    {
        return self::APP_URL . '/permalink/' . $resource . '/view/' . $id;
    }

    /**
     * Sends an HTTP request to the lexware API.
     *
     * @return array<mixed>
     * @throws LexwareException
     * @throws RateLimitException
     */
    public function sendRequest(string $endpoint, mixed $body = null, string $method = 'GET'): array
    {
        $url = self::BASE_URL . ltrim($endpoint, '/');

        $request = $this->requestFactory->createRequest($method, $url)
            ->withHeader('Authorization', 'Bearer ' . $this->apiKey)
            ->withHeader('Accept', 'application/json')
            ->withHeader('User-Agent', self::USER_AGENT);

        if ($body !== null && in_array($method, ['POST', 'PUT', 'PATCH'], true)) {
            $json = json_encode($body, JSON_THROW_ON_ERROR);
            $request = $request
                ->withHeader('Content-Type', 'application/json')
                ->withBody($this->streamFactory->createStream($json));
        }

        try {
            $response = $this->httpClient->sendRequest($request);
        } catch (ClientExceptionInterface $e) {
            throw new LexwareException(
                message: 'HTTP request failed: ' . $e->getMessage(),
                httpStatusCode: 0,
            );
        }

        $statusCode = $response->getStatusCode();

        if ($statusCode === 429) {
            throw new RateLimitException(
                message: 'Rate limit exceeded',
                responseBody: (string) $response->getBody(),
            );
        }

        if ($statusCode < 200 || $statusCode >= 300) {
            throw new LexwareException(
                message: sprintf('lexware API error %d for %s %s', $statusCode, $method, $endpoint),
                httpStatusCode: $statusCode,
                responseBody: (string) $response->getBody(),
            );
        }

        $responseBody = (string) $response->getBody();
        if (empty($responseBody)) {
            return [];
        }

        $decoded = json_decode($responseBody, true);
        if (!is_array($decoded)) {
            throw new LexwareException(
                message: 'Invalid JSON response from lexware API',
                httpStatusCode: $statusCode,
                responseBody: $responseBody,
            );
        }

        return $decoded;
    }

    /**
     * Downloads a binary file and returns its content with type and suggested file name.
     *
     * @param string $accept Accept header; selects the representation (e.g. application/xml for XRechnung).
     * @throws LexwareException
     * @throws RateLimitException
     */
    public function download(string $endpoint, string $accept = '*/*'): DownloadedFile
    {
        $url = self::BASE_URL . ltrim($endpoint, '/');

        $request = $this->requestFactory->createRequest('GET', $url)
            ->withHeader('Authorization', 'Bearer ' . $this->apiKey)
            ->withHeader('Accept', $accept)
            ->withHeader('User-Agent', self::USER_AGENT);

        try {
            $response = $this->httpClient->sendRequest($request);
        } catch (ClientExceptionInterface $e) {
            throw new LexwareException(
                message: 'File download failed: ' . $e->getMessage(),
                httpStatusCode: 0,
            );
        }

        $statusCode = $response->getStatusCode();

        if ($statusCode === 429) {
            throw new RateLimitException(responseBody: (string) $response->getBody());
        }

        if ($statusCode < 200 || $statusCode >= 300) {
            throw new LexwareException(
                message: sprintf('lexware API error %d for file download: %s', $statusCode, $endpoint),
                httpStatusCode: $statusCode,
                responseBody: (string) $response->getBody(),
            );
        }

        $disposition = $response->getHeaderLine('Content-Disposition');

        return new DownloadedFile(
            content: (string) $response->getBody(),
            contentType: $response->getHeaderLine('Content-Type'),
            fileName: $disposition !== '' ? DownloadedFile::parseFileName($disposition) : null,
        );
    }

    /**
     * Downloads a file as PDF and returns the raw bytes.
     *
     * @deprecated Use download(), which also returns the content type and file name.
     * @throws LexwareException
     * @throws RateLimitException
     */
    public function downloadFile(string $endpoint): string
    {
        return $this->download($endpoint, 'application/pdf')->content;
    }

    /**
     * Uploads a file via multipart/form-data.
     *
     * @return array<mixed>
     * @throws LexwareException
     * @throws RateLimitException
     */
    public function uploadFile(string $endpoint, string $filePath, string $type = 'voucher'): array
    {
        if (!file_exists($filePath)) {
            throw new LexwareException(
                message: sprintf('File not found: %s', $filePath),
                httpStatusCode: 0,
            );
        }

        $fileContent = file_get_contents($filePath);
        if ($fileContent === false) {
            throw new LexwareException(
                message: sprintf('Failed to read file: %s', $filePath),
                httpStatusCode: 0,
            );
        }

        $boundary = '----LexwareBoundary' . bin2hex(random_bytes(8));
        $body = "--{$boundary}\r\n"
            . "Content-Disposition: form-data; name=\"file\"; filename=\"" . basename($filePath) . "\"\r\n"
            . "Content-Type: application/pdf\r\n"
            . "\r\n"
            . $fileContent . "\r\n"
            . "--{$boundary}\r\n"
            . "Content-Disposition: form-data; name=\"type\"\r\n"
            . "\r\n"
            . $type . "\r\n"
            . "--{$boundary}--\r\n";

        $url = self::BASE_URL . ltrim($endpoint, '/');

        $request = $this->requestFactory->createRequest('POST', $url)
            ->withHeader('Authorization', 'Bearer ' . $this->apiKey)
            ->withHeader('Accept', 'application/json')
            ->withHeader('User-Agent', self::USER_AGENT)
            ->withHeader('Content-Type', 'multipart/form-data; boundary=' . $boundary)
            ->withBody($this->streamFactory->createStream($body));

        try {
            $response = $this->httpClient->sendRequest($request);
        } catch (ClientExceptionInterface $e) {
            throw new LexwareException(
                message: 'File upload failed: ' . $e->getMessage(),
                httpStatusCode: 0,
            );
        }

        $statusCode = $response->getStatusCode();

        if ($statusCode === 429) {
            throw new RateLimitException(responseBody: (string) $response->getBody());
        }

        if ($statusCode < 200 || $statusCode >= 300) {
            throw new LexwareException(
                message: sprintf('lexware API error %d for file upload: %s', $statusCode, $endpoint),
                httpStatusCode: $statusCode,
                responseBody: (string) $response->getBody(),
            );
        }

        $responseBody = (string) $response->getBody();
        if (empty($responseBody)) {
            return [];
        }

        $decoded = json_decode($responseBody, true);
        if (!is_array($decoded)) {
            throw new LexwareException(
                message: 'Invalid JSON response from lexware API',
                httpStatusCode: $statusCode,
                responseBody: $responseBody,
            );
        }

        return $decoded;
    }

    // --- Endpoint-Factories ---

    public function contacts(): \Four\LexwareApiClient\Endpoint\ContactEndpointInterface
    {
        return new \Four\LexwareApiClient\Endpoint\ContactEndpoint($this);
    }

    public function invoices(): \Four\LexwareApiClient\Endpoint\InvoiceEndpointInterface
    {
        return new \Four\LexwareApiClient\Endpoint\InvoiceEndpoint($this);
    }

    public function creditNotes(): \Four\LexwareApiClient\Endpoint\CreditNoteEndpointInterface
    {
        return new \Four\LexwareApiClient\Endpoint\CreditNoteEndpoint($this);
    }

    public function deliveryNotes(): \Four\LexwareApiClient\Endpoint\DeliveryNoteEndpointInterface
    {
        return new \Four\LexwareApiClient\Endpoint\DeliveryNoteEndpoint($this);
    }

    public function quotations(): \Four\LexwareApiClient\Endpoint\QuotationEndpointInterface
    {
        return new \Four\LexwareApiClient\Endpoint\QuotationEndpoint($this);
    }

    public function orderConfirmations(): \Four\LexwareApiClient\Endpoint\OrderConfirmationEndpointInterface
    {
        return new \Four\LexwareApiClient\Endpoint\OrderConfirmationEndpoint($this);
    }

    public function dunnings(): \Four\LexwareApiClient\Endpoint\DunningEndpointInterface
    {
        return new \Four\LexwareApiClient\Endpoint\DunningEndpoint($this);
    }

    public function downPaymentInvoices(): \Four\LexwareApiClient\Endpoint\DownPaymentInvoiceEndpointInterface
    {
        return new \Four\LexwareApiClient\Endpoint\DownPaymentInvoiceEndpoint($this);
    }

    public function articles(): \Four\LexwareApiClient\Endpoint\ArticleEndpointInterface
    {
        return new \Four\LexwareApiClient\Endpoint\ArticleEndpoint($this);
    }

    public function vouchers(): \Four\LexwareApiClient\Endpoint\VoucherEndpointInterface
    {
        return new \Four\LexwareApiClient\Endpoint\VoucherEndpoint($this);
    }

    public function files(): \Four\LexwareApiClient\Endpoint\FileEndpointInterface
    {
        return new \Four\LexwareApiClient\Endpoint\FileEndpoint($this);
    }

    public function payments(): \Four\LexwareApiClient\Endpoint\PaymentEndpointInterface
    {
        return new \Four\LexwareApiClient\Endpoint\PaymentEndpoint($this);
    }

    public function eventSubscriptions(): \Four\LexwareApiClient\Endpoint\EventSubscriptionEndpointInterface
    {
        return new \Four\LexwareApiClient\Endpoint\EventSubscriptionEndpoint($this);
    }

    public function voucherlist(): \Four\LexwareApiClient\Endpoint\VoucherlistEndpointInterface
    {
        return new \Four\LexwareApiClient\Endpoint\VoucherlistEndpoint($this);
    }

    public function countries(): \Four\LexwareApiClient\Endpoint\CountryEndpointInterface
    {
        return new \Four\LexwareApiClient\Endpoint\CountryEndpoint($this);
    }

    public function profile(): \Four\LexwareApiClient\Endpoint\ProfileEndpointInterface
    {
        return new \Four\LexwareApiClient\Endpoint\ProfileEndpoint($this);
    }

    public function paymentConditions(): \Four\LexwareApiClient\Endpoint\PaymentConditionEndpointInterface
    {
        return new \Four\LexwareApiClient\Endpoint\PaymentConditionEndpoint($this);
    }

    public function postingCategories(): \Four\LexwareApiClient\Endpoint\PostingCategoryEndpointInterface
    {
        return new \Four\LexwareApiClient\Endpoint\PostingCategoryEndpoint($this);
    }

    public function printLayouts(): \Four\LexwareApiClient\Endpoint\PrintLayoutEndpointInterface
    {
        return new \Four\LexwareApiClient\Endpoint\PrintLayoutEndpoint($this);
    }

    public function recurringTemplates(): \Four\LexwareApiClient\Endpoint\RecurringTemplateEndpointInterface
    {
        return new \Four\LexwareApiClient\Endpoint\RecurringTemplateEndpoint($this);
    }
}
