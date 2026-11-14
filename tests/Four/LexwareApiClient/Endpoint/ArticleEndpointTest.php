<?php

declare(strict_types=1);

namespace Four\LexwareApiClient\Tests\Endpoint;

use Four\LexwareApiClient\DTO\Article;
use Four\LexwareApiClient\Endpoint\ArticleEndpoint;
use Four\LexwareApiClient\Exception\LexwareException;
use Four\LexwareApiClient\LexwareClient;
use Http\Mock\Client as MockClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

class ArticleEndpointTest extends TestCase
{
    private MockClient $mockHttpClient;
    private Psr17Factory $psr17Factory;
    private ArticleEndpoint $endpoint;

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
        $this->endpoint = new ArticleEndpoint($client);
    }

    public function testCreateReturnsIdAndSendsJsonBody(): void
    {
        $this->addJsonResponse(201, '{"id":"a1","resourceUri":"/v1/articles/a1","version":1}');

        $result = $this->endpoint->create($this->article());

        self::assertSame('a1', $result['id']);
        self::assertSame(1, $result['version']);

        $request = $this->lastRequest();
        self::assertSame('POST', $request->getMethod());
        self::assertSame('/v1/articles', $request->getUri()->getPath());

        $body = json_decode((string) $request->getBody(), true);
        if (!is_array($body)) {
            self::fail('Request body is not valid JSON');
        }

        self::assertSame('Testartikel', $body['title']);
        self::assertSame(Article::TYPE_PRODUCT, $body['type']);
    }

    public function testCreateThrowsOnError(): void
    {
        $this->mockHttpClient->addResponse(new Response(400));

        $this->expectException(LexwareException::class);
        $this->endpoint->create($this->article());
    }

    public function testGetReturnsArticle(): void
    {
        $this->addJsonResponse(200, '{"id":"a1","title":"Testartikel"}');

        $result = $this->endpoint->get('a1');

        self::assertSame('a1', $result['id']);
        self::assertSame('Testartikel', $result['title']);

        $request = $this->lastRequest();
        self::assertSame('GET', $request->getMethod());
        self::assertSame('/v1/articles/a1', $request->getUri()->getPath());
    }

    public function testGetThrowsOn404(): void
    {
        $this->mockHttpClient->addResponse(new Response(404));

        $this->expectException(LexwareException::class);
        $this->endpoint->get('missing');
    }

    public function testUpdateSendsPutRequest(): void
    {
        $this->addJsonResponse(200, '{"id":"a1","version":2}');

        $result = $this->endpoint->update('a1', $this->article(version: 1));

        self::assertSame(2, $result['version']);

        $request = $this->lastRequest();
        self::assertSame('PUT', $request->getMethod());
        self::assertSame('/v1/articles/a1', $request->getUri()->getPath());
    }

    public function testDeleteSendsDeleteRequest(): void
    {
        $this->mockHttpClient->addResponse(new Response(204));

        $this->endpoint->delete('a1');

        $request = $this->lastRequest();
        self::assertSame('DELETE', $request->getMethod());
        self::assertSame('/v1/articles/a1', $request->getUri()->getPath());
    }

    public function testListBuildsPageAndFilterQuery(): void
    {
        $this->addJsonResponse(200, '{"content":[{"id":"a1"}],"totalElements":1,"totalPages":1,"number":2,"size":25,"first":false,"last":true}');

        $result = $this->endpoint->list(2, ['type' => Article::TYPE_PRODUCT]);

        self::assertSame([['id' => 'a1']], $result->content);
        self::assertSame(1, $result->totalElements);
        self::assertSame(2, $result->number);

        $request = $this->lastRequest();
        self::assertSame('GET', $request->getMethod());
        self::assertSame('/v1/articles', $request->getUri()->getPath());
        self::assertSame('page=2&type=PRODUCT', $request->getUri()->getQuery());
    }

    public function testListUsesPageZeroByDefault(): void
    {
        $this->addJsonResponse(200, '{"content":[],"totalElements":0,"totalPages":0,"number":0,"size":25,"first":true,"last":true}');

        $this->endpoint->list();

        $request = $this->lastRequest();
        self::assertSame('page=0', $request->getUri()->getQuery());
    }

    private function article(int $version = 0): Article
    {
        return new Article(
            title: 'Testartikel',
            type: Article::TYPE_PRODUCT,
            unitName: 'Stück',
            netPrice: 9.99,
            version: $version,
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
