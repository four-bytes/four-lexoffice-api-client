<?php

declare(strict_types=1);

namespace Four\LexofficeApiClient\Tests\Endpoint;

use Four\LexofficeApiClient\DTO\Quotation;
use Four\LexofficeApiClient\Endpoint\QuotationEndpoint;
use Four\LexofficeApiClient\Exception\LexofficeException;
use Four\LexofficeApiClient\LexofficeClient;
use Http\Mock\Client as MockClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

class QuotationEndpointTest extends TestCase
{
    private MockClient $mockHttpClient;
    private Psr17Factory $psr17Factory;
    private QuotationEndpoint $endpoint;

    protected function setUp(): void
    {
        $this->mockHttpClient = new MockClient();
        $this->psr17Factory = new Psr17Factory();
        $client = new LexofficeClient(
            'test-api-key',
            $this->mockHttpClient,
            $this->psr17Factory,
            $this->psr17Factory,
        );
        $this->endpoint = new QuotationEndpoint($client);
    }

    public function testCreateReturnsId(): void
    {
        $this->addJsonResponse(201, '{"id":"q1","resourceUri":"/v1/quotations/q1","version":1}');

        $result = $this->endpoint->create($this->quotation());

        self::assertSame('q1', $result['id']);
        self::assertSame(1, $result['version']);

        $request = $this->lastRequest();
        self::assertSame('POST', $request->getMethod());
        self::assertSame('/v1/quotations', $request->getUri()->getPath());
        self::assertSame('', $request->getUri()->getQuery());
    }

    public function testCreateWithFinalizeAddsQueryParameter(): void
    {
        $this->addJsonResponse(201, '{"id":"q1"}');

        $this->endpoint->create($this->quotation(), true);

        $request = $this->lastRequest();
        self::assertSame('finalize=true', $request->getUri()->getQuery());
    }

    public function testCreateThrowsOnError(): void
    {
        $this->mockHttpClient->addResponse(new Response(400));

        $this->expectException(LexofficeException::class);
        $this->endpoint->create($this->quotation());
    }

    public function testPursueBuildsPrecedingVoucherQuery(): void
    {
        $this->addJsonResponse(201, '{"id":"q2"}');

        $result = $this->endpoint->pursue('v1', $this->quotation());

        self::assertSame('q2', $result['id']);

        $request = $this->lastRequest();
        self::assertSame('/v1/quotations', $request->getUri()->getPath());
        self::assertSame('precedingSalesVoucherId=v1', $request->getUri()->getQuery());
    }

    public function testPursueWithFinalizeAddsBothQueryParameters(): void
    {
        $this->addJsonResponse(201, '{"id":"q2"}');

        $this->endpoint->pursue('v1', $this->quotation(), true);

        $request = $this->lastRequest();
        self::assertSame('precedingSalesVoucherId=v1&finalize=true', $request->getUri()->getQuery());
    }

    public function testGetReturnsQuotation(): void
    {
        $this->addJsonResponse(200, '{"id":"q1","voucherNumber":"AN-0001"}');

        $result = $this->endpoint->get('q1');

        self::assertSame('q1', $result['id']);
        self::assertSame('AN-0001', $result['voucherNumber']);

        $request = $this->lastRequest();
        self::assertSame('/v1/quotations/q1', $request->getUri()->getPath());
    }

    public function testGetThrowsOn404(): void
    {
        $this->mockHttpClient->addResponse(new Response(404));

        $this->expectException(LexofficeException::class);
        $this->endpoint->get('missing');
    }

    public function testDownloadPdfReturnsBytes(): void
    {
        $pdf = '%PDF-1.4 quotation';
        $this->mockHttpClient->addResponse(new Response(
            200,
            ['Content-Type' => 'application/pdf'],
            $this->psr17Factory->createStream($pdf),
        ));

        self::assertSame($pdf, $this->endpoint->downloadPdf('q1'));

        $request = $this->lastRequest();
        self::assertSame('/v1/quotations/q1/document', $request->getUri()->getPath());
    }

    private function quotation(): Quotation
    {
        return new Quotation(
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
