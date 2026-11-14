<?php

declare(strict_types=1);

namespace Four\LexwareApiClient\Tests\Endpoint;

use Four\LexwareApiClient\Endpoint\DownPaymentInvoiceEndpoint;
use Four\LexwareApiClient\Exception\LexwareException;
use Four\LexwareApiClient\LexwareClient;
use Http\Mock\Client as MockClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\TestCase;

class DownPaymentInvoiceEndpointTest extends TestCase
{
    private MockClient $mockHttpClient;
    private Psr17Factory $psr17Factory;
    private DownPaymentInvoiceEndpoint $endpoint;

    protected function setUp(): void
    {
        $this->mockHttpClient = new MockClient();
        $this->psr17Factory = new Psr17Factory();
        $client = new LexwareClient(
            'test-api-key',
            $this->mockHttpClient,
            $this->psr17Factory,
            $this->psr17Factory,
        );
        $this->endpoint = new DownPaymentInvoiceEndpoint($client);
    }

    public function testGetReturnsArray(): void
    {
        $body = $this->psr17Factory->createStream('{"id":"dp123","voucherNumber":"AP-0001"}');
        $this->mockHttpClient->addResponse(new Response(200, ['Content-Type' => 'application/json'], $body));

        $result = $this->endpoint->get('dp123');

        $this->assertSame('dp123', $result['id']);
        $this->assertSame('AP-0001', $result['voucherNumber']);
    }

    public function testGetThrowsOnError(): void
    {
        $this->mockHttpClient->addResponse(new Response(404));

        $this->expectException(LexwareException::class);
        $this->endpoint->get('nonexistent');
    }

    public function testDownloadPdfReturnsBytes(): void
    {
        $pdfBytes = '%PDF-1.4 fake content';
        $body = $this->psr17Factory->createStream($pdfBytes);
        $this->mockHttpClient->addResponse(new Response(200, ['Content-Type' => 'application/pdf'], $body));

        $result = $this->endpoint->downloadPdf('dp123');

        $this->assertSame($pdfBytes, $result);
    }
}
