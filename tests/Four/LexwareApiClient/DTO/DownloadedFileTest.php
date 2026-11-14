<?php

declare(strict_types=1);

namespace Four\LexwareApiClient\Tests\DTO;

use Four\LexwareApiClient\DTO\DownloadedFile;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class DownloadedFileTest extends TestCase
{
    /**
     * @return array<string, array{string, ?string}>
     */
    public static function dispositionProvider(): array
    {
        return [
            'quoted'          => ['attachment; filename="RE0001.pdf"', 'RE0001.pdf'],
            'unquoted'        => ['attachment; filename=RE0001.xml', 'RE0001.xml'],
            'rfc 5987'        => ["attachment; filename*=UTF-8''Rechnung%20M%C3%BCller.pdf", 'Rechnung Müller.pdf'],
            'rfc 5987 wins'   => ["attachment; filename=\"fallback.pdf\"; filename*=UTF-8''real.pdf", 'real.pdf'],
            'no filename'     => ['attachment', null],
            'empty quoted'    => ['attachment; filename=""', null],
        ];
    }

    #[DataProvider('dispositionProvider')]
    public function testParseFileName(string $header, ?string $expected): void
    {
        self::assertSame($expected, DownloadedFile::parseFileName($header));
    }
}
