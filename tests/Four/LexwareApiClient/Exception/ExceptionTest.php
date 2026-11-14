<?php

declare(strict_types=1);

namespace Four\LexwareApiClient\Tests\Exception;

use Four\LexwareApiClient\Exception\LexwareException;
use Four\LexwareApiClient\Exception\RateLimitException;
use PHPUnit\Framework\TestCase;

class ExceptionTest extends TestCase
{
    public function testLexwareExceptionStoresStatusCodeAndBody(): void
    {
        $ex = new LexwareException(
            message: 'API Fehler',
            httpStatusCode: 422,
            responseBody: '{"error":"Ungültige Daten"}',
        );

        self::assertSame('API Fehler', $ex->getMessage());
        self::assertSame(422, $ex->getHttpStatusCode());
        self::assertSame('{"error":"Ungültige Daten"}', $ex->getResponseBody());
        self::assertSame(422, $ex->getCode());
    }

    public function testRateLimitExceptionExtendsBse(): void
    {
        $ex = new RateLimitException();

        self::assertInstanceOf(LexwareException::class, $ex);
        self::assertSame(429, $ex->getHttpStatusCode());
        self::assertSame(429, $ex->getCode());
        self::assertStringContainsString('Rate limit', $ex->getMessage());
    }

    public function testRateLimitExceptionWithCustomMessage(): void
    {
        $ex = new RateLimitException(
            message: 'Zu viele Anfragen',
            responseBody: '{"message":"Too Many Requests"}',
        );

        self::assertSame('Zu viele Anfragen', $ex->getMessage());
        self::assertSame('{"message":"Too Many Requests"}', $ex->getResponseBody());
        self::assertSame(429, $ex->getHttpStatusCode());
    }
}
