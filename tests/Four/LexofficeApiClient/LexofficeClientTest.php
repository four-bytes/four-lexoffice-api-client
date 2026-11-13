<?php

declare(strict_types=1);

namespace Four\LexofficeApiClient\Tests;

use Four\LexofficeApiClient\Exception\LexofficeException;
use Four\LexofficeApiClient\Exception\RateLimitException;
use Four\LexofficeApiClient\LexofficeClient;
use Http\Mock\Client as MockClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\TestCase;

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
            'https://app.lexoffice.de/permalink/contacts/view/abc-123',
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
}
