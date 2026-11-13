<?php

declare(strict_types=1);

namespace Four\LexofficeApiClient\Tests\Endpoint;

use Four\LexofficeApiClient\Endpoint\FileEndpoint;
use Four\LexofficeApiClient\Exception\LexofficeException;
use Four\LexofficeApiClient\LexofficeClient;
use Http\Mock\Client as MockClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

class FileEndpointTest extends TestCase
{
    private MockClient $mockHttpClient;
    private Psr17Factory $psr17Factory;
    private FileEndpoint $endpoint;

    /** @var string[] */
    private array $tempFiles = [];

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
        $this->endpoint = new FileEndpoint($client);
    }

    protected function tearDown(): void
    {
        foreach ($this->tempFiles as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }

        $this->tempFiles = [];
    }

    public function testUploadToVoucherReturnsFileId(): void
    {
        $this->addJsonResponse(201, '{"id":"file1"}');

        $result = $this->endpoint->uploadToVoucher('v1', $this->createTempFile());

        self::assertSame('file1', $result);

        $request = $this->lastRequest();
        self::assertSame('POST', $request->getMethod());
        self::assertSame('/v1/vouchers/v1/files', $request->getUri()->getPath());
        self::assertStringContainsString('multipart/form-data', $request->getHeaderLine('Content-Type'));
    }

    public function testUploadToVoucherThrowsWhenIdIsMissing(): void
    {
        $this->addJsonResponse(200, '{}');

        $this->expectException(LexofficeException::class);
        $this->expectExceptionMessage('Kein "id" Feld');
        $this->endpoint->uploadToVoucher('v1', $this->createTempFile());
    }

    public function testUploadToVoucherThrowsWhenFileDoesNotExist(): void
    {
        $this->expectException(LexofficeException::class);
        $this->expectExceptionMessage('File not found');
        $this->endpoint->uploadToVoucher('v1', '/tmp/does-not-exist-lexoffice');
    }

    public function testUploadIncomingReturnsFileId(): void
    {
        $this->addJsonResponse(201, '{"id":"file2"}');

        $result = $this->endpoint->uploadIncoming($this->createTempFile());

        self::assertSame('file2', $result);

        $request = $this->lastRequest();
        self::assertSame('POST', $request->getMethod());
        self::assertSame('/v1/files', $request->getUri()->getPath());
    }

    public function testUploadIncomingThrowsWhenIdIsMissing(): void
    {
        $this->addJsonResponse(200, '{}');

        $this->expectException(LexofficeException::class);
        $this->expectExceptionMessage('Kein "id" Feld');
        $this->endpoint->uploadIncoming($this->createTempFile());
    }

    public function testUploadIncomingThrowsOnErrorResponse(): void
    {
        $this->mockHttpClient->addResponse(new Response(500));

        $this->expectException(LexofficeException::class);
        $this->endpoint->uploadIncoming($this->createTempFile());
    }

    private function createTempFile(): string
    {
        $filePath = tempnam(sys_get_temp_dir(), 'lexoffice-test-');
        if ($filePath === false) {
            self::fail('Could not create temporary file');
        }

        file_put_contents($filePath, '%PDF-1.4 test upload');
        $this->tempFiles[] = $filePath;

        return $filePath;
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
