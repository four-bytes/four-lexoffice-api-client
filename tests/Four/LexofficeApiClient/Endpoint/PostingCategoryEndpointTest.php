<?php

declare(strict_types=1);

namespace Four\LexofficeApiClient\Tests\Endpoint;

use Four\LexofficeApiClient\Endpoint\PostingCategoryEndpoint;
use Four\LexofficeApiClient\Exception\LexofficeException;
use Four\LexofficeApiClient\LexofficeClient;
use Http\Mock\Client as MockClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

class PostingCategoryEndpointTest extends TestCase
{
    private MockClient $mockHttpClient;
    private Psr17Factory $psr17Factory;
    private PostingCategoryEndpoint $endpoint;

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
        $this->endpoint = new PostingCategoryEndpoint($client);
    }

    public function testListReturnsPostingCategories(): void
    {
        $this->addJsonResponse(200, '[{"id":"cat1","type":"outgo","name":"Wareneinkauf"}]');

        $result = $this->endpoint->list();

        self::assertSame([['id' => 'cat1', 'type' => 'outgo', 'name' => 'Wareneinkauf']], $result);

        $request = $this->lastRequest();
        self::assertSame('GET', $request->getMethod());
        self::assertSame('/v1/posting-categories', $request->getUri()->getPath());
    }

    public function testListThrowsOnError(): void
    {
        $this->mockHttpClient->addResponse(new Response(500));

        $this->expectException(LexofficeException::class);
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
