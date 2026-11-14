<?php

declare(strict_types=1);

namespace Four\LexwareApiClient\Tests\Endpoint;

use Four\LexwareApiClient\DTO\DeliveryNote;
use Four\LexwareApiClient\Endpoint\DeliveryNoteEndpoint;
use Four\LexwareApiClient\Exception\LexwareException;
use Four\LexwareApiClient\LexwareClient;
use Http\Mock\Client as MockClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

class DeliveryNoteEndpointTest extends TestCase
{
    private MockClient $mockHttpClient;
    private Psr17Factory $psr17Factory;
    private DeliveryNoteEndpoint $endpoint;

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
        $this->endpoint = new DeliveryNoteEndpoint($client);
    }

    public function testCreateReturnsId(): void
    {
        $this->addJsonResponse(201, '{"id":"dn1","resourceUri":"/v1/delivery-notes/dn1","version":1}');

        $result = $this->endpoint->create($this->deliveryNote());

        self::assertSame('dn1', $result['id']);
        self::assertSame(1, $result['version']);

        $request = $this->lastRequest();
        self::assertSame('POST', $request->getMethod());
        self::assertSame('/v1/delivery-notes', $request->getUri()->getPath());
        self::assertSame('', $request->getUri()->getQuery());
    }

    public function testCreateWithFinalizeAddsQueryParameter(): void
    {
        $this->addJsonResponse(201, '{"id":"dn1"}');

        $this->endpoint->create($this->deliveryNote(), true);

        $request = $this->lastRequest();
        self::assertSame('finalize=true', $request->getUri()->getQuery());
    }

    public function testCreateThrowsOnError(): void
    {
        $this->mockHttpClient->addResponse(new Response(400));

        $this->expectException(LexwareException::class);
        $this->endpoint->create($this->deliveryNote());
    }

    public function testGetReturnsDeliveryNote(): void
    {
        $this->addJsonResponse(200, '{"id":"dn1","voucherNumber":"DN-0001"}');

        $result = $this->endpoint->get('dn1');

        self::assertSame('dn1', $result['id']);
        self::assertSame('DN-0001', $result['voucherNumber']);

        $request = $this->lastRequest();
        self::assertSame('/v1/delivery-notes/dn1', $request->getUri()->getPath());
    }

    public function testGetThrowsOn404(): void
    {
        $this->mockHttpClient->addResponse(new Response(404));

        $this->expectException(LexwareException::class);
        $this->endpoint->get('missing');
    }

    public function testDownloadPdfReturnsBytes(): void
    {
        $pdf = '%PDF-1.4 delivery note';
        $this->mockHttpClient->addResponse(new Response(
            200,
            ['Content-Type' => 'application/pdf'],
            $this->psr17Factory->createStream($pdf),
        ));

        self::assertSame($pdf, $this->endpoint->downloadPdf('dn1'));

        $request = $this->lastRequest();
        self::assertSame('/v1/delivery-notes/dn1/document', $request->getUri()->getPath());
    }

    private function deliveryNote(): DeliveryNote
    {
        return new DeliveryNote(
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
