<?php

declare(strict_types=1);

namespace Four\LexwareApiClient\Tests\Endpoint;

use Four\LexwareApiClient\DTO\OrderConfirmation;
use Four\LexwareApiClient\Endpoint\OrderConfirmationEndpoint;
use Four\LexwareApiClient\Exception\LexwareException;
use Four\LexwareApiClient\LexwareClient;
use Http\Mock\Client as MockClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

class OrderConfirmationEndpointTest extends TestCase
{
    private MockClient $mockHttpClient;
    private Psr17Factory $psr17Factory;
    private OrderConfirmationEndpoint $endpoint;

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
        $this->endpoint = new OrderConfirmationEndpoint($client);
    }

    public function testCreateReturnsId(): void
    {
        $this->addJsonResponse(201, '{"id":"oc1","resourceUri":"/v1/order-confirmations/oc1","version":1}');

        $result = $this->endpoint->create($this->orderConfirmation());

        self::assertSame('oc1', $result['id']);
        self::assertSame(1, $result['version']);

        $request = $this->lastRequest();
        self::assertSame('POST', $request->getMethod());
        self::assertSame('/v1/order-confirmations', $request->getUri()->getPath());
        self::assertSame('', $request->getUri()->getQuery());
    }

    public function testCreateWithFinalizeAddsQueryParameter(): void
    {
        $this->addJsonResponse(201, '{"id":"oc1"}');

        $this->endpoint->create($this->orderConfirmation(), true);

        $request = $this->lastRequest();
        self::assertSame('finalize=true', $request->getUri()->getQuery());
    }

    public function testCreateThrowsOnError(): void
    {
        $this->mockHttpClient->addResponse(new Response(400));

        $this->expectException(LexwareException::class);
        $this->endpoint->create($this->orderConfirmation());
    }

    public function testPursueBuildsPrecedingVoucherQuery(): void
    {
        $this->addJsonResponse(201, '{"id":"oc2"}');

        $result = $this->endpoint->pursue('v1', $this->orderConfirmation());

        self::assertSame('oc2', $result['id']);

        $request = $this->lastRequest();
        self::assertSame('/v1/order-confirmations', $request->getUri()->getPath());
        self::assertSame('precedingSalesVoucherId=v1', $request->getUri()->getQuery());
    }

    public function testPursueWithFinalizeAddsBothQueryParameters(): void
    {
        $this->addJsonResponse(201, '{"id":"oc2"}');

        $this->endpoint->pursue('v1', $this->orderConfirmation(), true);

        $request = $this->lastRequest();
        self::assertSame('precedingSalesVoucherId=v1&finalize=true', $request->getUri()->getQuery());
    }

    public function testGetReturnsOrderConfirmation(): void
    {
        $this->addJsonResponse(200, '{"id":"oc1","voucherNumber":"AB-0001"}');

        $result = $this->endpoint->get('oc1');

        self::assertSame('oc1', $result['id']);
        self::assertSame('AB-0001', $result['voucherNumber']);

        $request = $this->lastRequest();
        self::assertSame('/v1/order-confirmations/oc1', $request->getUri()->getPath());
    }

    public function testGetThrowsOn404(): void
    {
        $this->mockHttpClient->addResponse(new Response(404));

        $this->expectException(LexwareException::class);
        $this->endpoint->get('missing');
    }

    public function testDownloadPdfReturnsBytes(): void
    {
        $pdf = '%PDF-1.4 order confirmation';
        $this->mockHttpClient->addResponse(new Response(
            200,
            ['Content-Type' => 'application/pdf'],
            $this->psr17Factory->createStream($pdf),
        ));

        self::assertSame($pdf, $this->endpoint->downloadPdf('oc1'));

        $request = $this->lastRequest();
        self::assertSame('/v1/order-confirmations/oc1/file', $request->getUri()->getPath());
        self::assertSame('application/pdf', $request->getHeaderLine('Accept'));
    }

    public function testDownloadFileReturnsContentTypeAndFileName(): void
    {
        $xml = '<?xml version="1.0"?><Invoice/>';
        $this->mockHttpClient->addResponse(new Response(
            200,
            [
                'Content-Type'        => 'application/xml',
                'Content-Disposition' => 'attachment; filename="oc1.xml"',
            ],
            $this->psr17Factory->createStream($xml),
        ));

        $file = $this->endpoint->downloadFile('oc1', 'application/xml');

        self::assertSame($xml, $file->content);
        self::assertSame('application/xml', $file->contentType);
        self::assertSame('oc1.xml', $file->fileName);

        $request = $this->lastRequest();
        self::assertSame('GET', $request->getMethod());
        self::assertSame('/v1/order-confirmations/oc1/file', $request->getUri()->getPath());
        self::assertSame('application/xml', $request->getHeaderLine('Accept'));
    }

    public function testDownloadFileUsesWildcardAcceptByDefault(): void
    {
        $this->mockHttpClient->addResponse(new Response(200, ['Content-Type' => 'application/pdf'], '%PDF'));

        $file = $this->endpoint->downloadFile('oc1');

        self::assertSame('application/pdf', $file->contentType);
        self::assertNull($file->fileName);
        $request = $this->lastRequest();
        self::assertSame('*/*', $request->getHeaderLine('Accept'));
    }

    private function orderConfirmation(): OrderConfirmation
    {
        return new OrderConfirmation(
            voucherDate: '2026-01-15',
            addressName: 'Max Mustermann',
            addressStreet: 'Musterstraße 1',
            addressCity: 'Berlin',
            addressZip: '10115',
            addressCountryCode: 'DE',
            lineItems: [],
        );
    }

    private function addJsonResponse(int $status, string $json): void
    {
        $this->mockHttpClient->addResponse(new Response(
            $status,
            ['Content-Type' => 'application/json'],
            $this->psr17Factory->createStream($json),
        ));
    }

    private function lastRequest(): RequestInterface
    {
        $request = $this->mockHttpClient->getLastRequest();
        if ($request === false) {
            self::fail('No request was sent');
        }

        return $request;
    }
}
