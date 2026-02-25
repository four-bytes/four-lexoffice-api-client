<?php

declare(strict_types=1);

namespace Four\LexofficeApiClient\Tests\DTO;

use Four\LexofficeApiClient\DTO\InvoiceLineItem;
use PHPUnit\Framework\TestCase;

class InvoiceLineItemTest extends TestCase
{
    public function testToApiArrayProducesCorrectStructure(): void
    {
        $item = new InvoiceLineItem(
            name:             'Etsy-Bestellung',
            quantity:         2.0,
            unitName:         'Stück',
            unitNetPrice:     19.99,
            taxRatePercentage: 19.0,
        );

        $data = $item->toApiArray();

        self::assertSame('custom', $data['type']);
        self::assertSame('Etsy-Bestellung', $data['name']);
        self::assertSame(2.0, $data['quantity']);
        self::assertSame('Stück', $data['unitName']);
        self::assertSame('EUR', $data['unitPrice']['currency']);
        self::assertSame(19.99, $data['unitPrice']['netAmount']);
        self::assertSame(19.0, $data['unitPrice']['taxRatePercentage']);
    }

    public function testToApiArrayOmitsNullOptionals(): void
    {
        $item = new InvoiceLineItem(
            name:             'Service',
            quantity:         1.0,
            unitName:         'Std.',
            unitNetPrice:     100.00,
            taxRatePercentage: 19.0,
        );

        $data = $item->toApiArray();

        self::assertArrayNotHasKey('description', $data);
        self::assertArrayNotHasKey('discountPercentage', $data);
    }

    public function testToApiArrayIncludesDiscount(): void
    {
        $item = new InvoiceLineItem(
            name:               'Rabatt-Artikel',
            quantity:           1.0,
            unitName:           'Stück',
            unitNetPrice:       50.00,
            taxRatePercentage:  19.0,
            type:               InvoiceLineItem::TYPE_MATERIAL,
            description:        'Mit Rabatt',
            discountPercentage: 10.0,
        );

        $data = $item->toApiArray();

        self::assertSame('material', $data['type']);
        self::assertSame('Mit Rabatt', $data['description']);
        self::assertSame(10.0, $data['discountPercentage']);
    }

    public function testTypeConstants(): void
    {
        self::assertSame('custom', InvoiceLineItem::TYPE_CUSTOM);
        self::assertSame('material', InvoiceLineItem::TYPE_MATERIAL);
        self::assertSame('service', InvoiceLineItem::TYPE_SERVICE);
        self::assertSame('text', InvoiceLineItem::TYPE_TEXT);
    }
}
