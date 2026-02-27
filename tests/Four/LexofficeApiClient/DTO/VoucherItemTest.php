<?php

declare(strict_types=1);

namespace Four\LexofficeApiClient\Tests\DTO;

use Four\LexofficeApiClient\DTO\VoucherItem;
use PHPUnit\Framework\TestCase;

class VoucherItemTest extends TestCase
{
    public function testDefaultCategoryIsInlandSales(): void
    {
        $item = new VoucherItem(
            amount: 29.90,
            taxAmount: 4.77,
            taxRatePercent: 19,
        );

        self::assertSame(VoucherItem::CATEGORY_INLAND_SALES, $item->categoryId);
    }

    public function testToApiArrayFormatsAmounts(): void
    {
        $item = new VoucherItem(
            amount: 29.9,
            taxAmount: 4.771,
            taxRatePercent: 19,
            categoryId: VoucherItem::CATEGORY_EU_SALES,
        );

        $data = $item->toApiArray();

        self::assertSame('29.90', $data['amount']);
        self::assertSame('4.77', $data['taxAmount']);
        self::assertSame(19, $data['taxRatePercent']);
        self::assertSame(VoucherItem::CATEGORY_EU_SALES, $data['categoryId']);
    }

    public function testKnownCategoryIdConstants(): void
    {
        self::assertSame('8f8664a8-fd86-11e1-a21f-0800200c9a66', VoucherItem::CATEGORY_INLAND_SALES);
        self::assertSame('4ebd965a-7126-416c-9d8c-a5c9366ee473', VoucherItem::CATEGORY_EU_SALES);
        self::assertSame('9075a4e3-66de-4795-a016-3889feca0d20', VoucherItem::CATEGORY_EU_DEALER_VAT_FREE);
        self::assertSame('93d24c20-ea84-424e-a731-5e1b78d1e6a9', VoucherItem::CATEGORY_WORLD_EXPORT);
        self::assertSame('8f8664a7-fd86-11e1-a21f-0800200c9a66', VoucherItem::CATEGORY_SERVICE_FEES);
        self::assertSame('8f8664a1-fd86-11e1-a21f-0800200c9a66', VoucherItem::CATEGORY_REVENUE);
    }

    public function testZeroTaxRate(): void
    {
        $item = new VoucherItem(
            amount: 50.00,
            taxAmount: 0.00,
            taxRatePercent: 0,
            categoryId: VoucherItem::CATEGORY_WORLD_EXPORT,
        );

        $data = $item->toApiArray();

        self::assertSame('50.00', $data['amount']);
        self::assertSame('0.00', $data['taxAmount']);
        self::assertSame(0, $data['taxRatePercent']);
    }

    public function testFractionalTaxRate(): void
    {
        $item = new VoucherItem(
            amount: 40.89,
            taxAmount: 8.31,
            taxRatePercent: 25.5,
            categoryId: VoucherItem::CATEGORY_EU_SALES,
        );

        $data = $item->toApiArray();

        self::assertSame('40.89', $data['amount']);
        self::assertSame('8.31', $data['taxAmount']);
        self::assertSame(25.5, $data['taxRatePercent']);
    }
}
