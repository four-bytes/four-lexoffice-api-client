<?php

declare(strict_types=1);

namespace Four\LexwareApiClient\Tests\DTO;

use Four\LexwareApiClient\DTO\PagedResponse;
use PHPUnit\Framework\TestCase;

class PagedResponseTest extends TestCase
{
    public function testFromArrayMapsAllFields(): void
    {
        $data = [
            'content'       => [['id' => 'abc'], ['id' => 'def']],
            'totalElements' => 42,
            'totalPages'    => 2,
            'number'        => 0,
            'size'          => 25,
            'first'         => true,
            'last'          => false,
        ];

        $response = PagedResponse::fromArray($data);

        self::assertCount(2, $response->content);
        self::assertSame(42, $response->totalElements);
        self::assertSame(2, $response->totalPages);
        self::assertSame(0, $response->number);
        self::assertSame(25, $response->size);
        self::assertTrue($response->first);
        self::assertFalse($response->last);
    }

    public function testFromArrayHandlesMissingFields(): void
    {
        $response = PagedResponse::fromArray([]);

        self::assertSame([], $response->content);
        self::assertSame(0, $response->totalElements);
        self::assertSame(0, $response->totalPages);
        self::assertSame(0, $response->number);
        self::assertSame(0, $response->size);
        self::assertTrue($response->first);
        self::assertTrue($response->last);
    }
}
