<?php

declare(strict_types=1);

namespace Four\LexofficeApiClient\Tests;

use Four\LexofficeApiClient\Endpoint\ArticleEndpointInterface;
use Four\LexofficeApiClient\Endpoint\ContactEndpointInterface;
use Four\LexofficeApiClient\Endpoint\CountryEndpointInterface;
use Four\LexofficeApiClient\Endpoint\CreditNoteEndpointInterface;
use Four\LexofficeApiClient\Endpoint\DeliveryNoteEndpointInterface;
use Four\LexofficeApiClient\Endpoint\DownPaymentInvoiceEndpointInterface;
use Four\LexofficeApiClient\Endpoint\DunningEndpointInterface;
use Four\LexofficeApiClient\Endpoint\EventSubscriptionEndpointInterface;
use Four\LexofficeApiClient\Endpoint\FileEndpointInterface;
use Four\LexofficeApiClient\Endpoint\InvoiceEndpointInterface;
use Four\LexofficeApiClient\Endpoint\OrderConfirmationEndpointInterface;
use Four\LexofficeApiClient\Endpoint\PaymentConditionEndpointInterface;
use Four\LexofficeApiClient\Endpoint\PaymentEndpointInterface;
use Four\LexofficeApiClient\Endpoint\PostingCategoryEndpointInterface;
use Four\LexofficeApiClient\Endpoint\PrintLayoutEndpointInterface;
use Four\LexofficeApiClient\Endpoint\ProfileEndpointInterface;
use Four\LexofficeApiClient\Endpoint\QuotationEndpointInterface;
use Four\LexofficeApiClient\Endpoint\RecurringTemplateEndpointInterface;
use Four\LexofficeApiClient\Endpoint\VoucherlistEndpointInterface;
use Four\LexofficeApiClient\Endpoint\VoucherEndpointInterface;
use Four\LexofficeApiClient\Exception\LexofficeException;
use Four\LexofficeApiClient\Exception\RateLimitException;
use Four\LexofficeApiClient\LexofficeClient;
use Http\Client\Exception\NetworkException;
use Http\Discovery\Psr18ClientDiscovery;
use Http\Discovery\Strategy\DiscoveryStrategy;
use Http\Mock\Client as MockClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;

class LexofficeClientTest extends TestCase
{
    private MockClient $mockHttpClient;
    private Psr17Factory $psr17Factory;
    private LexofficeClient $client;

    protected function setUp(): void
    {
        $this->mockHttpClient = new MockClient();
        $this->psr17Factory = new Psr17Factory();
        $this->client = new LexofficeClient(
            'test-api-key',
            $this->mockHttpClient,
            $this->psr17Factory,
            $this->psr17Factory,
        );
    }

    public function testDeeplinkUrl(): void
    {
        $this->assertSame(
            'https://app.lexware.de/permalink/contacts/view/abc-123',
            $this->client->deeplinkUrl('contacts', 'abc-123'),
        );
    }

    public function testSendRequestGet(): void
    {
        $body = $this->psr17Factory->createStream('{"id":"abc123","name":"Test"}');
        $this->mockHttpClient->addResponse(new Response(200, ['Content-Type' => 'application/json'], $body));

        $result = $this->client->sendRequest('contacts/abc123');

        $this->assertSame(['id' => 'abc123', 'name' => 'Test'], $result);
    }

    public function testSendRequestPost(): void
    {
        $body = $this->psr17Factory->createStream('{"id":"new123"}');
        $this->mockHttpClient->addResponse(new Response(201, ['Content-Type' => 'application/json'], $body));

        $result = $this->client->sendRequest('contacts', ['name' => 'Test GmbH'], 'POST');

        $this->assertSame(['id' => 'new123'], $result);
    }

    public function testSendRequestReturnsEmptyArrayOn204(): void
    {
        $this->mockHttpClient->addResponse(new Response(204));

        $result = $this->client->sendRequest('contacts/abc123', null, 'DELETE');

        $this->assertSame([], $result);
    }

    public function testSendRequestThrowsLexofficeExceptionOn4xx(): void
    {
        $body = $this->psr17Factory->createStream('{"message":"Not found"}');
        $this->mockHttpClient->addResponse(new Response(404, [], $body));

        $this->expectException(LexofficeException::class);
        $this->client->sendRequest('contacts/missing');
    }

    public function testSendRequestThrowsRateLimitExceptionOn429(): void
    {
        $this->mockHttpClient->addResponse(new Response(429));

        $this->expectException(RateLimitException::class);
        $this->client->sendRequest('contacts');
    }

    public function testDownloadFileSuccess(): void
    {
        $pdfBytes = '%PDF-1.4 fake pdf content';
        $body = $this->psr17Factory->createStream($pdfBytes);
        $this->mockHttpClient->addResponse(new Response(200, ['Content-Type' => 'application/pdf'], $body));

        $result = $this->client->downloadFile('invoices/abc123/document');

        $this->assertSame($pdfBytes, $result);
    }

    public function testDownloadFileThrowsOnError(): void
    {
        $this->mockHttpClient->addResponse(new Response(404));

        $this->expectException(LexofficeException::class);
        $this->client->downloadFile('invoices/missing/document');
    }

    public function testUploadFileThrowsWhenFileNotFound(): void
    {
        $this->expectException(LexofficeException::class);
        $this->client->uploadFile('files', '/nonexistent/path/file.pdf');
    }

    public function testUploadFileSuccess(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'lexoffice_test_') . '.pdf';
        file_put_contents($tmpFile, '%PDF-1.4 test content');

        $body = $this->psr17Factory->createStream('{"id":"file123"}');
        $this->mockHttpClient->addResponse(new Response(200, ['Content-Type' => 'application/json'], $body));

        try {
            $result = $this->client->uploadFile('files', $tmpFile, 'voucher');
            $this->assertSame(['id' => 'file123'], $result);
        } finally {
            unlink($tmpFile);
        }
    }

    public function testSendRequestThrowsLexofficeExceptionOnNetworkError(): void
    {
        $request = $this->psr17Factory->createRequest('GET', 'https://api.lexoffice.io/v1/contacts');
        $this->mockHttpClient->addException(new NetworkException('Connection refused', $request));

        try {
            $this->client->sendRequest('contacts');
            self::fail('Expected LexofficeException');
        } catch (LexofficeException $e) {
            self::assertSame(0, $e->getHttpStatusCode());
            self::assertStringContainsString('HTTP request failed', $e->getMessage());
        }
    }

    public function testSendRequestThrowsLexofficeExceptionOnInvalidJson(): void
    {
        $body = $this->psr17Factory->createStream('this-is-not-json');
        $this->mockHttpClient->addResponse(new Response(200, [], $body));

        try {
            $this->client->sendRequest('contacts');
            self::fail('Expected LexofficeException');
        } catch (LexofficeException $e) {
            self::assertSame(200, $e->getHttpStatusCode());
            self::assertSame('this-is-not-json', $e->getResponseBody());
        }
    }

    public function testSendRequestThrowsLexofficeExceptionOn5xx(): void
    {
        $body = $this->psr17Factory->createStream('{"message":"Internal Server Error"}');
        $this->mockHttpClient->addResponse(new Response(500, [], $body));

        try {
            $this->client->sendRequest('contacts');
            self::fail('Expected LexofficeException');
        } catch (LexofficeException $e) {
            self::assertSame(500, $e->getHttpStatusCode());
        }
    }

    public function testSendRequestReturnsEmptyArrayOnEmpty2xxBody(): void
    {
        $this->mockHttpClient->addResponse(new Response(200));

        self::assertSame([], $this->client->sendRequest('contacts'));
    }

    public function testDownloadFileThrowsRateLimitExceptionOn429(): void
    {
        $this->mockHttpClient->addResponse(new Response(429));

        $this->expectException(RateLimitException::class);
        $this->client->downloadFile('invoices/abc123/document');
    }

    public function testDownloadFileThrowsLexofficeExceptionOnNetworkError(): void
    {
        $request = $this->psr17Factory->createRequest('GET', 'https://api.lexoffice.io/v1/invoices/abc123/document');
        $this->mockHttpClient->addException(new NetworkException('Connection refused', $request));

        try {
            $this->client->downloadFile('invoices/abc123/document');
            self::fail('Expected LexofficeException');
        } catch (LexofficeException $e) {
            self::assertSame(0, $e->getHttpStatusCode());
            self::assertStringContainsString('PDF download failed', $e->getMessage());
        }
    }

    public function testUploadFileThrowsWhenFileCannotBeRead(): void
    {
        $socketPath = sys_get_temp_dir() . '/lexoffice_' . uniqid('', true) . '.sock';
        $server = stream_socket_server('unix://' . $socketPath);
        if ($server === false) {
            self::fail('Could not create test socket');
        }

        set_error_handler(static fn(int $errno, string $errstr, string $errfile, int $errline): bool => true);

        try {
            $this->expectException(LexofficeException::class);
            $this->client->uploadFile('files', $socketPath);
        } finally {
            restore_error_handler();
            fclose($server);
            unlink($socketPath);
        }
    }

    public function testUploadFileThrowsLexofficeExceptionOn4xx(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'lexoffice_test_') . '.pdf';
        file_put_contents($tmpFile, '%PDF-1.4 test content');

        $body = $this->psr17Factory->createStream('{"message":"Bad Request"}');
        $this->mockHttpClient->addResponse(new Response(400, [], $body));

        try {
            $this->client->uploadFile('files', $tmpFile, 'voucher');
            self::fail('Expected LexofficeException');
        } catch (LexofficeException $e) {
            self::assertSame(400, $e->getHttpStatusCode());
        } finally {
            unlink($tmpFile);
        }
    }

    public function testUploadFileThrowsRateLimitExceptionOn429(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'lexoffice_test_') . '.pdf';
        file_put_contents($tmpFile, '%PDF-1.4 test content');

        $this->mockHttpClient->addResponse(new Response(429));

        try {
            $this->client->uploadFile('files', $tmpFile, 'voucher');
            self::fail('Expected RateLimitException');
        } catch (RateLimitException $e) {
            self::assertSame(429, $e->getHttpStatusCode());
        } finally {
            unlink($tmpFile);
        }
    }

    public function testUploadFileReturnsEmptyArrayOnEmptyBody(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'lexoffice_test_') . '.pdf';
        file_put_contents($tmpFile, '%PDF-1.4 test content');

        $this->mockHttpClient->addResponse(new Response(200));

        try {
            self::assertSame([], $this->client->uploadFile('files', $tmpFile, 'voucher'));
        } finally {
            unlink($tmpFile);
        }
    }

    public function testUploadFileThrowsLexofficeExceptionOnInvalidJson(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'lexoffice_test_') . '.pdf';
        file_put_contents($tmpFile, '%PDF-1.4 test content');

        $body = $this->psr17Factory->createStream('not-json');
        $this->mockHttpClient->addResponse(new Response(200, [], $body));

        try {
            $this->client->uploadFile('files', $tmpFile, 'voucher');
            self::fail('Expected LexofficeException');
        } catch (LexofficeException $e) {
            self::assertSame(200, $e->getHttpStatusCode());
            self::assertSame('not-json', $e->getResponseBody());
        } finally {
            unlink($tmpFile);
        }
    }

    public function testUploadFileThrowsLexofficeExceptionOnNetworkError(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'lexoffice_test_') . '.pdf';
        file_put_contents($tmpFile, '%PDF-1.4 test content');

        $request = $this->psr17Factory->createRequest('POST', 'https://api.lexoffice.io/v1/files');
        $this->mockHttpClient->addException(new NetworkException('Connection refused', $request));

        try {
            $this->client->uploadFile('files', $tmpFile, 'voucher');
            self::fail('Expected LexofficeException');
        } catch (LexofficeException $e) {
            self::assertSame(0, $e->getHttpStatusCode());
            self::assertStringContainsString('File upload failed', $e->getMessage());
        } finally {
            unlink($tmpFile);
        }
    }

    public function testCreateBuildsClientAndWiresAllEndpointFactories(): void
    {
        /** @var array<int, string> $originalStrategies */
        $originalStrategies = Psr18ClientDiscovery::getStrategies();
        Psr18ClientDiscovery::prependStrategy(MockClientDiscoveryStrategy::class);

        try {
            $client = LexofficeClient::create('test-api-key');

            self::assertInstanceOf(LexofficeClient::class, $client);
            self::assertInstanceOf(ContactEndpointInterface::class, $client->contacts());
            self::assertInstanceOf(InvoiceEndpointInterface::class, $client->invoices());
            self::assertInstanceOf(CreditNoteEndpointInterface::class, $client->creditNotes());
            self::assertInstanceOf(DeliveryNoteEndpointInterface::class, $client->deliveryNotes());
            self::assertInstanceOf(QuotationEndpointInterface::class, $client->quotations());
            self::assertInstanceOf(OrderConfirmationEndpointInterface::class, $client->orderConfirmations());
            self::assertInstanceOf(DunningEndpointInterface::class, $client->dunnings());
            self::assertInstanceOf(DownPaymentInvoiceEndpointInterface::class, $client->downPaymentInvoices());
            self::assertInstanceOf(ArticleEndpointInterface::class, $client->articles());
            self::assertInstanceOf(VoucherEndpointInterface::class, $client->vouchers());
            self::assertInstanceOf(FileEndpointInterface::class, $client->files());
            self::assertInstanceOf(PaymentEndpointInterface::class, $client->payments());
            self::assertInstanceOf(EventSubscriptionEndpointInterface::class, $client->eventSubscriptions());
            self::assertInstanceOf(VoucherlistEndpointInterface::class, $client->voucherlist());
            self::assertInstanceOf(CountryEndpointInterface::class, $client->countries());
            self::assertInstanceOf(ProfileEndpointInterface::class, $client->profile());
            self::assertInstanceOf(PaymentConditionEndpointInterface::class, $client->paymentConditions());
            self::assertInstanceOf(PostingCategoryEndpointInterface::class, $client->postingCategories());
            self::assertInstanceOf(PrintLayoutEndpointInterface::class, $client->printLayouts());
            self::assertInstanceOf(RecurringTemplateEndpointInterface::class, $client->recurringTemplates());
        } finally {
            Psr18ClientDiscovery::setStrategies($originalStrategies);
        }
    }
}

final class MockClientDiscoveryStrategy implements DiscoveryStrategy
{
    /**
     * @param string $type
     *
     * @return array<array{class: class-string, condition: array<class-string>}>
     */
    public static function getCandidates($type): array
    {
        if ($type === ClientInterface::class) {
            return [['class' => MockClient::class, 'condition' => [MockClient::class]]];
        }

        return [];
    }
}
