<?php

declare(strict_types=1);

namespace Four\LexwareApiClient\Tests\DTO;

use Four\LexwareApiClient\DTO\Article;
use PHPUnit\Framework\TestCase;

class ArticleTest extends TestCase
{
    public function testToApiArrayIncludesRequiredFields(): void
    {
        $article = new Article(
            title:    'Testprodukt',
            type:     Article::TYPE_PRODUCT,
            unitName: 'Stück',
            netPrice: 9.99,
        );

        $data = $article->toApiArray();

        self::assertSame('Testprodukt', $data['title']);
        self::assertSame(Article::TYPE_PRODUCT, $data['type']);
        self::assertSame('Stück', $data['unitName']);
        self::assertSame(9.99, $data['price']['netPrice']);
        self::assertSame('NET', $data['price']['leadingPrice']);
        self::assertSame(19, $data['price']['taxRate']);
        self::assertSame(0, $data['version']);
    }

    public function testToApiArrayOmitsNullFields(): void
    {
        $article = new Article(
            title:    'Service',
            type:     Article::TYPE_SERVICE,
            unitName: 'Std.',
            netPrice: 80.00,
        );

        $data = $article->toApiArray();

        self::assertArrayNotHasKey('description', $data);
        self::assertArrayNotHasKey('articleNumber', $data);
        self::assertArrayNotHasKey('gtin', $data);
        self::assertArrayNotHasKey('note', $data);
    }

    public function testToApiArrayIncludesOptionalFields(): void
    {
        $article = new Article(
            title:         'Produkt mit Details',
            type:          Article::TYPE_PRODUCT,
            unitName:      'kg',
            netPrice:      4.50,
            leadingPrice:  'GROSS',
            taxRate:       7,
            description:   'Beschreibung',
            articleNumber: 'ART-001',
            gtin:          '4012345678901',
            note:          'Interne Notiz',
            version:       1,
        );

        $data = $article->toApiArray();

        self::assertSame('Beschreibung', $data['description']);
        self::assertSame('ART-001', $data['articleNumber']);
        self::assertSame('4012345678901', $data['gtin']);
        self::assertSame('Interne Notiz', $data['note']);
        self::assertSame(1, $data['version']);
        self::assertSame('GROSS', $data['price']['leadingPrice']);
        self::assertSame(7, $data['price']['taxRate']);
    }

    public function testTypeConstants(): void
    {
        self::assertSame('PRODUCT', Article::TYPE_PRODUCT);
        self::assertSame('SERVICE', Article::TYPE_SERVICE);
    }

    public function testFromArrayParsesAllFields(): void
    {
        $article = Article::fromArray([
            'title'         => 'Testprodukt',
            'type'          => Article::TYPE_SERVICE,
            'unitName'      => 'Std.',
            'price'         => [
                'netPrice'     => 80.5,
                'leadingPrice' => 'GROSS',
                'taxRate'      => 7,
            ],
            'description'   => 'Beschreibung',
            'articleNumber' => 'ART-001',
            'gtin'          => '4012345678901',
            'note'          => 'Interne Notiz',
            'version'       => 3,
        ]);

        self::assertSame('Testprodukt', $article->title);
        self::assertSame(Article::TYPE_SERVICE, $article->type);
        self::assertSame('Std.', $article->unitName);
        self::assertSame(80.5, $article->netPrice);
        self::assertSame('GROSS', $article->leadingPrice);
        self::assertSame(7, $article->taxRate);
        self::assertSame('Beschreibung', $article->description);
        self::assertSame('ART-001', $article->articleNumber);
        self::assertSame('4012345678901', $article->gtin);
        self::assertSame('Interne Notiz', $article->note);
        self::assertSame(3, $article->version);
    }

    public function testFromArrayUsesDefaultsForMissingFields(): void
    {
        $article = Article::fromArray([]);

        self::assertSame('', $article->title);
        self::assertSame(Article::TYPE_PRODUCT, $article->type);
        self::assertSame('', $article->unitName);
        self::assertSame(0.0, $article->netPrice);
        self::assertSame('NET', $article->leadingPrice);
        self::assertSame(19, $article->taxRate);
        self::assertNull($article->description);
        self::assertNull($article->articleNumber);
        self::assertNull($article->gtin);
        self::assertNull($article->note);
        self::assertSame(0, $article->version);
    }
}
