<?php

declare(strict_types=1);

namespace Four\LexwareApiClient\Tests\Endpoint;

use Four\LexwareApiClient\Endpoint\RecurringTemplateEndpoint;
use Four\LexwareApiClient\Exception\LexwareException;
use Four\LexwareApiClient\LexwareClient;
use Http\Mock\Client as MockClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

class RecurringTemplateEndpointTest extends TestCase
{
    private MockClient $mockHttpClient;
    private Psr17Factory $psr17Factory;
    private RecurringTemplateEndpoint $endpoint;

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
        $this->endpoint = new RecurringTemplateEndpoint($client);
    }

    public function testListBuildsPageQuery(): void
    {
        $this->addJsonResponse(200, '{"content":[],"totalElements":0,"totalPages":0,"number":3,"size":25,"first":false,"last":true}');

        $result = $this->endpoint->list(3);

        self::assertSame([], $result['content'] ?? null);
        self::assertSame(3, $result['number'] ?? null);

        $request = $this->lastRequest();
        self::assertSame('GET', $request->getMethod());
        self::assertSame('/v1/recurring-templates', $request->getUri()->getPath());
        self::assertSame('page=3', $request->getUri()->getQuery());
    }

    public function testListUsesPageZeroByDefault(): void
    {
        $this->addJsonResponse(200, '{"content":[],"totalElements":0,"totalPages":0,"number":0,"size":25,"first":true,"last":true}');

        $this->endpoint->list();

        $request = $this->lastRequest();
        self::assertSame('page=0', $request->getUri()->getQuery());
    }

    public function testListThrowsOnError(): void
    {
        $this->mockHttpClient->addResponse(new Response(500));

        $this->expectException(LexwareException::class);
        $this->endpoint->list();
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
