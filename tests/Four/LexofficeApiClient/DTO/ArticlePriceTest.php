<?php

declare(strict_types=1);

namespace Four\LexofficeApiClient\Tests\DTO;

use Four\LexofficeApiClient\DTO\ArticlePrice;
use PHPUnit\Framework\TestCase;

class ArticlePriceTest extends TestCase
{
    public function testToApiArrayProducesRequiredFields(): void
    {
        $price = new ArticlePrice(netPrice: 10.0);

        self::assertSame(
            [
                'netPrice'     => 10.0,
                'leadingPrice' => 'NET',
                'taxRate'      => 19,
            ],
            $price->toApiArray(),
        );
    }

    public function testToApiArrayIncludesOptionalFields(): void
    {
        $price = new ArticlePrice(
            netPrice:     10.0,
            leadingPrice: 'GROSS',
            taxRate:      7,
            grossPrice:   10.7,
        );

        $data = $price->toApiArray();

        self::assertSame('GROSS', $data['leadingPrice']);
        self::assertSame(7, $data['taxRate']);
        self::assertSame(10.7, $data['grossPrice']);
    }

    public function testToApiArrayOmitsNullGrossPrice(): void
    {
        $price = new ArticlePrice(netPrice: 10.0);

        self::assertArrayNotHasKey('grossPrice', $price->toApiArray());
    }
}
