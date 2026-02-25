<?php

declare(strict_types=1);

namespace Four\LexofficeApiClient\Tests\DTO;

use Four\LexofficeApiClient\DTO\VoucherItem;
use PHPUnit\Framework\TestCase;

class VoucherItemCategoryTest extends TestCase
{
    public function testNewCategoryConstants(): void
    {
        self::assertSame('8f8664a0-fd86-11e1-a21f-0800200c9a66', VoucherItem::CATEGORY_SERVICES);
        self::assertSame('7c112b66-0565-479c-bc18-5845e080880a', VoucherItem::CATEGORY_DISTANCE_SALES_EU);
        self::assertSame('4ebd965a-7126-416c-9d8c-a5c9366ee473', VoucherItem::CATEGORY_DISTANCE_SALES_EU_TAXABLE);
        self::assertSame('ef5b1a6e-f690-4004-9a19-91276348894f', VoucherItem::CATEGORY_THIRD_COUNTRY_SERVICE);
        self::assertSame('7a1efa0e-6283-4cbf-9583-8e88d3ba5960', VoucherItem::CATEGORY_SMALL_BUSINESS);
    }

    public function testSmallBusinessCategoryUsable(): void
    {
        $item = new VoucherItem(
            amount:         19.99,
            taxAmount:      0.00,
            taxRatePercent: 0,
            categoryId:     VoucherItem::CATEGORY_SMALL_BUSINESS,
        );

        $data = $item->toApiArray();
        self::assertSame(VoucherItem::CATEGORY_SMALL_BUSINESS, $data['categoryId']);
        self::assertSame('0.00', $data['taxAmount']);
    }

    public function testDistanceSalesEuCategory(): void
    {
        $item = new VoucherItem(
            amount:         50.00,
            taxAmount:      9.50,
            taxRatePercent: 19,
            categoryId:     VoucherItem::CATEGORY_DISTANCE_SALES_EU,
        );

        self::assertSame(VoucherItem::CATEGORY_DISTANCE_SALES_EU, $item->categoryId);
    }
}
