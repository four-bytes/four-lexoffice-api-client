<?php

declare(strict_types=1);

namespace Four\LexofficeApiClient\Tests\Endpoint;

use Four\LexofficeApiClient\DTO\Voucher;
use Four\LexofficeApiClient\Endpoint\VoucherEndpoint;
use Four\LexofficeApiClient\Exception\LexofficeException;
use Four\LexofficeApiClient\LexofficeClient;
use Http\Mock\Client as MockClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

class VoucherEndpointTest extends TestCase
{
    private MockClient $mockHttpClient;
    private Psr17Factory $psr17Factory;
    private VoucherEndpoint $endpoint;

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
        $this->endpoint = new VoucherEndpoint($client);
    }

    public function testGetReturnsVoucher(): void
    {
        $this->addJsonResponse(200, '{"id":"v1","voucherNumber":"RE-0001"}');

        $result = $this->endpoint->get('v1');

        self::assertSame('v1', $result['id']);
        self::assertSame('RE-0001', $result['voucherNumber']);

        $request = $this->lastRequest();
        self::assertSame('GET', $request->getMethod());
        self::assertSame('/v1/vouchers/v1', $request->getUri()->getPath());
    }

    public function testGetThrowsOn404(): void
    {
        $this->mockHttpClient->addResponse(new Response(404));

        $this->expectException(LexofficeException::class);
        $this->endpoint->get('missing');
    }

    public function testCreateReturnsIdAndSendsJsonBody(): void
    {
        $this->addJsonResponse(201, '{"id":"v1","resourceUri":"/v1/vouchers/v1"}');

        $result = $this->endpoint->create($this->voucher());

        self::assertSame('v1', $result);

        $request = $this->lastRequest();
        self::assertSame('POST', $request->getMethod());
        self::assertSame('/v1/vouchers', $request->getUri()->getPath());

        $body = json_decode((string) $request->getBody(), true);
        if (!is_array($body)) {
            self::fail('Request body is not valid JSON');
        }

        self::assertSame('salesinvoice', $body['type']);
        self::assertSame('RE-0001', $body['voucherNumber']);
    }

    public function testCreateThrowsWhenIdIsMissing(): void
    {
        $this->addJsonResponse(200, '{"resourceUri":"/v1/vouchers/v1"}');

        $this->expectException(LexofficeException::class);
        $this->expectExceptionMessage('Kein "id" Feld');
        $this->endpoint->create($this->voucher());
    }

    public function testUpdateSendsPutRequest(): void
    {
        $this->addJsonResponse(200, '{"id":"v1","voucherNumber":"RE-0002"}');

        $result = $this->endpoint->update('v1', $this->voucher());

        self::assertSame('RE-0002', $result['voucherNumber']);

        $request = $this->lastRequest();
        self::assertSame('PUT', $request->getMethod());
        self::assertSame('/v1/vouchers/v1', $request->getUri()->getPath());
    }

    private function voucher(): Voucher
    {
        return new Voucher(
            type: 'salesinvoice',
            voucherNumber: 'RE-0001',
            voucherDate: '2026-01-15',
            totalGrossAmount: 119.0,
            totalTaxAmount: 19.0,
            contactId: 'c1',
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
