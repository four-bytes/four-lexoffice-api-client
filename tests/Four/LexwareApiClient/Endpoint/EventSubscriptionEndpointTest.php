<?php

declare(strict_types=1);

namespace Four\LexwareApiClient\Tests\Endpoint;

use Four\LexwareApiClient\DTO\EventSubscription;
use Four\LexwareApiClient\Endpoint\EventSubscriptionEndpoint;
use Four\LexwareApiClient\Exception\LexwareException;
use Four\LexwareApiClient\LexwareClient;
use Http\Mock\Client as MockClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

class EventSubscriptionEndpointTest extends TestCase
{
    private MockClient $mockHttpClient;
    private Psr17Factory $psr17Factory;
    private EventSubscriptionEndpoint $endpoint;

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
        $this->endpoint = new EventSubscriptionEndpoint($client);
    }

    public function testCreateReturnsIdAndSendsJsonBody(): void
    {
        $this->addJsonResponse(201, '{"id":"sub1"}');

        $subscription = new EventSubscription(
            callbackUrl: 'https://example.com/webhook',
            eventTypes: [EventSubscription::EVENT_INVOICE_CREATED],
        );

        $result = $this->endpoint->create($subscription);

        self::assertSame('sub1', $result['id']);

        $request = $this->lastRequest();
        self::assertSame('POST', $request->getMethod());
        self::assertSame('/v1/event-subscriptions', $request->getUri()->getPath());

        $body = json_decode((string) $request->getBody(), true);
        if (!is_array($body)) {
            self::fail('Request body is not valid JSON');
        }

        self::assertSame('https://example.com/webhook', $body['callbackUrl']);
        self::assertSame(['invoice.created'], $body['eventTypes']);
        self::assertTrue($body['active']);
    }

    public function testGetReturnsSubscription(): void
    {
        $this->addJsonResponse(200, '{"id":"sub1","callbackUrl":"https://example.com/webhook"}');

        $result = $this->endpoint->get('sub1');

        self::assertSame('sub1', $result['id']);
        self::assertSame('https://example.com/webhook', $result['callbackUrl']);

        $request = $this->lastRequest();
        self::assertSame('/v1/event-subscriptions/sub1', $request->getUri()->getPath());
    }

    public function testGetThrowsOn404(): void
    {
        $this->mockHttpClient->addResponse(new Response(404));

        $this->expectException(LexwareException::class);
        $this->endpoint->get('missing');
    }

    public function testListReturnsSubscriptions(): void
    {
        $this->addJsonResponse(200, '[{"id":"sub1"},{"id":"sub2"}]');

        $result = $this->endpoint->list();

        self::assertSame([['id' => 'sub1'], ['id' => 'sub2']], $result);

        $request = $this->lastRequest();
        self::assertSame('GET', $request->getMethod());
        self::assertSame('/v1/event-subscriptions', $request->getUri()->getPath());
    }

    public function testDeleteSendsDeleteRequest(): void
    {
        $this->mockHttpClient->addResponse(new Response(204));

        $this->endpoint->delete('sub1');

        $request = $this->lastRequest();
        self::assertSame('DELETE', $request->getMethod());
        self::assertSame('/v1/event-subscriptions/sub1', $request->getUri()->getPath());
    }

    public function testDeleteThrowsOnError(): void
    {
        $this->mockHttpClient->addResponse(new Response(404));

        $this->expectException(LexwareException::class);
        $this->endpoint->delete('missing');
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
