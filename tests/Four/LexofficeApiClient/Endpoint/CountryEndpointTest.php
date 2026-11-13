<?php

declare(strict_types=1);

namespace Four\LexofficeApiClient\Tests\Endpoint;

use Four\LexofficeApiClient\Endpoint\CountryEndpoint;
use Four\LexofficeApiClient\Exception\LexofficeException;
use Four\LexofficeApiClient\LexofficeClient;
use Http\Mock\Client as MockClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

class CountryEndpointTest extends TestCase
{
    private MockClient $mockHttpClient;
    private Psr17Factory $psr17Factory;
    private CountryEndpoint $endpoint;

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
        $this->endpoint = new CountryEndpoint($client);
    }

    public function testListReturnsCountries(): void
    {
        $this->addJsonResponse(200, '[{"countryCode":"DE","name":"Deutschland"}]');

        $result = $this->endpoint->list();

        self::assertSame([['countryCode' => 'DE', 'name' => 'Deutschland']], $result);

        $request = $this->lastRequest();
        self::assertSame('GET', $request->getMethod());
        self::assertSame('/v1/countries', $request->getUri()->getPath());
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
