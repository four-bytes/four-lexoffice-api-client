<?php

declare(strict_types=1);

namespace Four\LexofficeApiClient;

use Four\Http\Configuration\ClientConfig;
use Four\Http\Configuration\RetryConfig;
use Four\Http\Factory\HttpClientFactory;
use Four\LexofficeApiClient\Exception\LexofficeException;
use Four\LexofficeApiClient\Exception\RateLimitException;
use Four\RateLimit\RateLimitConfiguration;
use Four\RateLimit\RateLimiterFactory;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

class LexofficeClient
{
    private const BASE_URL = 'https://api.lexware.io/v1/';
    private const APP_URL = 'https://app.lexware.de';
    private const USER_AGENT = 'four-lexoffice-api-client/0.3';

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
     * Builds a deeplink to a resource in the lexoffice web app.
     *
     * @param string $resource Resource type (e.g. "contacts", "invoices", "vouchers").
     * @param string $id       Resource id.
     */
    public function deeplinkUrl(string $resource, string $id): string
    {
        return self::APP_URL . '/permalink/' . $resource . '/view/' . $id;
    }

    /**
     * Sends an HTTP request to the lexoffice API.
     *
     * @return array<mixed>
     * @throws LexofficeException
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
            throw new LexofficeException(
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
            throw new LexofficeException(
                message: sprintf('lexoffice API error %d for %s %s', $statusCode, $method, $endpoint),
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
            throw new LexofficeException(
                message: 'Invalid JSON response from lexoffice API',
                httpStatusCode: $statusCode,
                responseBody: $responseBody,
            );
        }

        return $decoded;
    }

    /**
     * Downloads a PDF from the API and returns the raw bytes.
     *
     * @throws LexofficeException
     * @throws RateLimitException
     */
    public function downloadFile(string $endpoint): string
    {
        $url = self::BASE_URL . ltrim($endpoint, '/');

        $request = $this->requestFactory->createRequest('GET', $url)
            ->withHeader('Authorization', 'Bearer ' . $this->apiKey)
            ->withHeader('Accept', 'application/pdf')
            ->withHeader('User-Agent', self::USER_AGENT);

        try {
            $response = $this->httpClient->sendRequest($request);
        } catch (ClientExceptionInterface $e) {
            throw new LexofficeException(
                message: 'PDF download failed: ' . $e->getMessage(),
                httpStatusCode: 0,
            );
        }

        $statusCode = $response->getStatusCode();

        if ($statusCode === 429) {
            throw new RateLimitException(responseBody: (string) $response->getBody());
        }

        if ($statusCode < 200 || $statusCode >= 300) {
            throw new LexofficeException(
                message: sprintf('lexoffice API error %d for PDF download: %s', $statusCode, $endpoint),
                httpStatusCode: $statusCode,
                responseBody: (string) $response->getBody(),
            );
        }

        return (string) $response->getBody();
    }

    /**
     * Uploads a file via multipart/form-data.
     *
     * @return array<mixed>
     * @throws LexofficeException
     * @throws RateLimitException
     */
    public function uploadFile(string $endpoint, string $filePath, string $type = 'voucher'): array
    {
        if (!file_exists($filePath)) {
            throw new LexofficeException(
                message: sprintf('File not found: %s', $filePath),
                httpStatusCode: 0,
            );
        }

        $fileContent = file_get_contents($filePath);
        if ($fileContent === false) {
            throw new LexofficeException(
                message: sprintf('Failed to read file: %s', $filePath),
                httpStatusCode: 0,
            );
        }

        $boundary = '----LexofficeBoundary' . bin2hex(random_bytes(8));
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
            throw new LexofficeException(
                message: 'File upload failed: ' . $e->getMessage(),
                httpStatusCode: 0,
            );
        }

        $statusCode = $response->getStatusCode();

        if ($statusCode === 429) {
            throw new RateLimitException(responseBody: (string) $response->getBody());
        }

        if ($statusCode < 200 || $statusCode >= 300) {
            throw new LexofficeException(
                message: sprintf('lexoffice API error %d for file upload: %s', $statusCode, $endpoint),
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
            throw new LexofficeException(
                message: 'Invalid JSON response from lexoffice API',
                httpStatusCode: $statusCode,
                responseBody: $responseBody,
            );
        }

        return $decoded;
    }

    // --- Endpoint-Factories ---

    public function contacts(): \Four\LexofficeApiClient\Endpoint\ContactEndpointInterface
    {
        return new \Four\LexofficeApiClient\Endpoint\ContactEndpoint($this);
    }

    public function invoices(): \Four\LexofficeApiClient\Endpoint\InvoiceEndpointInterface
    {
        return new \Four\LexofficeApiClient\Endpoint\InvoiceEndpoint($this);
    }

    public function creditNotes(): \Four\LexofficeApiClient\Endpoint\CreditNoteEndpointInterface
    {
        return new \Four\LexofficeApiClient\Endpoint\CreditNoteEndpoint($this);
    }

    public function deliveryNotes(): \Four\LexofficeApiClient\Endpoint\DeliveryNoteEndpointInterface
    {
        return new \Four\LexofficeApiClient\Endpoint\DeliveryNoteEndpoint($this);
    }

    public function quotations(): \Four\LexofficeApiClient\Endpoint\QuotationEndpointInterface
    {
        return new \Four\LexofficeApiClient\Endpoint\QuotationEndpoint($this);
    }

    public function orderConfirmations(): \Four\LexofficeApiClient\Endpoint\OrderConfirmationEndpointInterface
    {
        return new \Four\LexofficeApiClient\Endpoint\OrderConfirmationEndpoint($this);
    }

    public function dunnings(): \Four\LexofficeApiClient\Endpoint\DunningEndpointInterface
    {
        return new \Four\LexofficeApiClient\Endpoint\DunningEndpoint($this);
    }

    public function downPaymentInvoices(): \Four\LexofficeApiClient\Endpoint\DownPaymentInvoiceEndpointInterface
    {
        return new \Four\LexofficeApiClient\Endpoint\DownPaymentInvoiceEndpoint($this);
    }

    public function articles(): \Four\LexofficeApiClient\Endpoint\ArticleEndpointInterface
    {
        return new \Four\LexofficeApiClient\Endpoint\ArticleEndpoint($this);
    }

    public function vouchers(): \Four\LexofficeApiClient\Endpoint\VoucherEndpointInterface
    {
        return new \Four\LexofficeApiClient\Endpoint\VoucherEndpoint($this);
    }

    public function files(): \Four\LexofficeApiClient\Endpoint\FileEndpointInterface
    {
        return new \Four\LexofficeApiClient\Endpoint\FileEndpoint($this);
    }

    public function payments(): \Four\LexofficeApiClient\Endpoint\PaymentEndpointInterface
    {
        return new \Four\LexofficeApiClient\Endpoint\PaymentEndpoint($this);
    }

    public function eventSubscriptions(): \Four\LexofficeApiClient\Endpoint\EventSubscriptionEndpointInterface
    {
        return new \Four\LexofficeApiClient\Endpoint\EventSubscriptionEndpoint($this);
    }

    public function voucherlist(): \Four\LexofficeApiClient\Endpoint\VoucherlistEndpointInterface
    {
        return new \Four\LexofficeApiClient\Endpoint\VoucherlistEndpoint($this);
    }

    public function countries(): \Four\LexofficeApiClient\Endpoint\CountryEndpointInterface
    {
        return new \Four\LexofficeApiClient\Endpoint\CountryEndpoint($this);
    }

    public function profile(): \Four\LexofficeApiClient\Endpoint\ProfileEndpointInterface
    {
        return new \Four\LexofficeApiClient\Endpoint\ProfileEndpoint($this);
    }

    public function paymentConditions(): \Four\LexofficeApiClient\Endpoint\PaymentConditionEndpointInterface
    {
        return new \Four\LexofficeApiClient\Endpoint\PaymentConditionEndpoint($this);
    }

    public function postingCategories(): \Four\LexofficeApiClient\Endpoint\PostingCategoryEndpointInterface
    {
        return new \Four\LexofficeApiClient\Endpoint\PostingCategoryEndpoint($this);
    }

    public function printLayouts(): \Four\LexofficeApiClient\Endpoint\PrintLayoutEndpointInterface
    {
        return new \Four\LexofficeApiClient\Endpoint\PrintLayoutEndpoint($this);
    }

    public function recurringTemplates(): \Four\LexofficeApiClient\Endpoint\RecurringTemplateEndpointInterface
    {
        return new \Four\LexofficeApiClient\Endpoint\RecurringTemplateEndpoint($this);
    }
}
