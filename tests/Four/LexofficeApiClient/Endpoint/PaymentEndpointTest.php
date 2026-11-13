<?php

declare(strict_types=1);

namespace Four\LexofficeApiClient\Tests\Endpoint;

use Four\LexofficeApiClient\DTO\Payment;
use Four\LexofficeApiClient\Endpoint\PaymentEndpoint;
use Four\LexofficeApiClient\Exception\LexofficeException;
use Four\LexofficeApiClient\LexofficeClient;
use Http\Mock\Client as MockClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

class PaymentEndpointTest extends TestCase
{
    private MockClient $mockHttpClient;
    private Psr17Factory $psr17Factory;
    private PaymentEndpoint $endpoint;

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
        $this->endpoint = new PaymentEndpoint($client);
    }

    public function testGetReturnsPaymentDto(): void
    {
        $this->addJsonResponse(200, '{"openAmount":119.0,"paymentStatus":"openRevenue","voucherType":"invoice","voucherStatus":"open","paymentItems":[],"paidDate":null}');

        $payment = $this->endpoint->get('v1');

        self::assertSame(119.0, $payment->openAmount);
        self::assertSame(Payment::STATUS_OPEN_REVENUE, $payment->paymentStatus);
        self::assertSame('invoice', $payment->voucherType);
        self::assertSame('open', $payment->voucherStatus);
        self::assertSame([], $payment->paymentItems);
        self::assertNull($payment->paidDate);

        $request = $this->lastRequest();
        self::assertSame('GET', $request->getMethod());
        self::assertSame('/v1/payments/v1', $request->getUri()->getPath());
    }

    public function testGetUsesDefaultsForMissingFields(): void
    {
        $this->addJsonResponse(200, '{}');

        $payment = $this->endpoint->get('v1');

        self::assertSame(0.0, $payment->openAmount);
        self::assertSame('', $payment->paymentStatus);
        self::assertSame([], $payment->paymentItems);
    }

    public function testGetThrowsOn404(): void
    {
        $this->mockHttpClient->addResponse(new Response(404));

        $this->expectException(LexofficeException::class);
        $this->endpoint->get('missing');
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
