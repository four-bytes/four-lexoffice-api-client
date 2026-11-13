<?php

declare(strict_types=1);

namespace Four\LexofficeApiClient\Tests\Endpoint;

use Four\LexofficeApiClient\DTO\Contact;
use Four\LexofficeApiClient\Endpoint\ContactEndpoint;
use Four\LexofficeApiClient\Exception\LexofficeException;
use Four\LexofficeApiClient\LexofficeClient;
use Http\Mock\Client as MockClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

class ContactEndpointTest extends TestCase
{
    private MockClient $mockHttpClient;
    private Psr17Factory $psr17Factory;
    private ContactEndpoint $endpoint;

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
        $this->endpoint = new ContactEndpoint($client);
    }

    public function testGetReturnsContact(): void
    {
        $this->addJsonResponse(200, '{"id":"c1","version":1}');

        $result = $this->endpoint->get('c1');

        self::assertSame('c1', $result['id']);

        $request = $this->lastRequest();
        self::assertSame('GET', $request->getMethod());
        self::assertSame('/v1/contacts/c1', $request->getUri()->getPath());
    }

    public function testGetThrowsOn404(): void
    {
        $this->mockHttpClient->addResponse(new Response(404));

        $this->expectException(LexofficeException::class);
        $this->endpoint->get('missing');
    }

    public function testCreateReturnsId(): void
    {
        $this->addJsonResponse(201, '{"id":"c1","resourceUri":"/v1/contacts/c1"}');

        $result = $this->endpoint->create(new Contact());

        self::assertSame('c1', $result);

        $request = $this->lastRequest();
        self::assertSame('POST', $request->getMethod());
        self::assertSame('/v1/contacts', $request->getUri()->getPath());
    }

    public function testCreateThrowsWhenIdIsMissing(): void
    {
        $this->addJsonResponse(200, '{"resourceUri":"/v1/contacts/c1"}');

        $this->expectException(LexofficeException::class);
        $this->expectExceptionMessage('Kein "id" Feld');
        $this->endpoint->create(new Contact());
    }

    public function testUpdateSendsPutAndReturnsId(): void
    {
        $this->mockHttpClient->addResponse(new Response(204));

        $result = $this->endpoint->update('c1', new Contact());

        self::assertSame('c1', $result);

        $request = $this->lastRequest();
        self::assertSame('PUT', $request->getMethod());
        self::assertSame('/v1/contacts/c1', $request->getUri()->getPath());
    }

    public function testUpsertUpdatesWhenExistingIdGiven(): void
    {
        $this->mockHttpClient->addResponse(new Response(204));

        $result = $this->endpoint->upsert(new Contact(), 'c1');

        self::assertSame('c1', $result);

        $request = $this->lastRequest();
        self::assertSame('PUT', $request->getMethod());
        self::assertSame('/v1/contacts/c1', $request->getUri()->getPath());
    }

    public function testUpsertCreatesWhenNoIdGiven(): void
    {
        $this->addJsonResponse(201, '{"id":"c2"}');

        $result = $this->endpoint->upsert(new Contact());

        self::assertSame('c2', $result);

        $request = $this->lastRequest();
        self::assertSame('POST', $request->getMethod());
        self::assertSame('/v1/contacts', $request->getUri()->getPath());
    }

    public function testFindByEmailReturnsFirstContactAndBuildsQuery(): void
    {
        $this->addJsonResponse(200, '{"content":[{"id":"c1","email":"max@example.com"}]}');

        $result = $this->endpoint->findByEmail('max@example.com');

        self::assertSame('c1', $result['id'] ?? null);

        $request = $this->lastRequest();
        self::assertSame('/v1/contacts', $request->getUri()->getPath());
        self::assertSame('email=max%40example.com', $request->getUri()->getQuery());
    }

    public function testFindByEmailReturnsNullWhenNoMatch(): void
    {
        $this->addJsonResponse(200, '{"content":[]}');

        self::assertNull($this->endpoint->findByEmail('nobody@example.com'));
    }

    public function testFindByNameBuildsQuery(): void
    {
        $this->addJsonResponse(200, '{"content":[{"id":"c1"}],"totalElements":1,"totalPages":1,"number":0,"size":25,"first":true,"last":true}');

        $result = $this->endpoint->findByName('Max Mustermann');

        self::assertSame([['id' => 'c1']], $result->content);

        $request = $this->lastRequest();
        self::assertSame('/v1/contacts', $request->getUri()->getPath());
        self::assertSame('name=Max+Mustermann', $request->getUri()->getQuery());
    }

    public function testListBuildsPageAndFilterQuery(): void
    {
        $this->addJsonResponse(200, '{"content":[],"totalElements":0,"totalPages":0,"number":1,"size":25,"first":false,"last":true}');

        $result = $this->endpoint->list(1, ['name' => 'ACME']);

        self::assertSame([], $result->content);
        self::assertSame(1, $result->number);

        $request = $this->lastRequest();
        self::assertSame('/v1/contacts', $request->getUri()->getPath());
        self::assertSame('page=1&name=ACME', $request->getUri()->getQuery());
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
