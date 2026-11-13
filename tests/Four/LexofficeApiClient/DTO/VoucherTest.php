<?php

declare(strict_types=1);

namespace Four\LexofficeApiClient\Tests\DTO;

use Four\LexofficeApiClient\DTO\Voucher;
use Four\LexofficeApiClient\DTO\VoucherItem;
use PHPUnit\Framework\TestCase;

class VoucherTest extends TestCase
{
    public function testToApiArrayBasic(): void
    {
        $item = new VoucherItem(
            amount: 29.90,
            taxAmount: 4.77,
            taxRatePercent: 19,
        );

        $voucher = new Voucher(
            type: 'salesinvoice',
            voucherNumber: '2026100001',
            voucherDate: '2026-02-25',
            totalGrossAmount: 29.90,
            totalTaxAmount: 4.77,
            contactId: 'test-uuid-123',
            remark: 'Bestellung #12345',
            shippingDate: '2026-02-26',
            voucherItems: [$item],
        );

        $data = $voucher->toApiArray();

        self::assertSame('salesinvoice', $data['type']);
        self::assertSame('2026100001', $data['voucherNumber']);
        self::assertSame('2026-02-25', $data['voucherDate']);
        self::assertSame('29.90', $data['totalGrossAmount']);
        self::assertSame('4.77', $data['totalTaxAmount']);
        self::assertSame('gross', $data['taxType']);
        self::assertFalse($data['useCollectiveContact']);
        self::assertSame('test-uuid-123', $data['contactId']);
        self::assertSame('Bestellung #12345', $data['remark']);
        self::assertSame('2026-02-26', $data['shippingDate']);
        self::assertCount(1, $data['voucherItems']);
    }

    public function testRemarkOmittedWhenEmpty(): void
    {
        $voucher = new Voucher(
            type: 'salesinvoice',
            voucherNumber: '2026100002',
            voucherDate: '2026-02-25',
            totalGrossAmount: 10.00,
            totalTaxAmount: 1.60,
            contactId: 'some-id',
        );

        $data = $voucher->toApiArray();

        self::assertArrayNotHasKey('remark', $data);
        self::assertArrayNotHasKey('shippingDate', $data);
    }

    public function testAmountFormattedWithTwoDecimals(): void
    {
        $voucher = new Voucher(
            type: 'salesinvoice',
            voucherNumber: '2026100003',
            voucherDate: '2026-02-25',
            totalGrossAmount: 100.0,
            totalTaxAmount: 15.966,
            contactId: 'some-id',
        );

        $data = $voucher->toApiArray();

        self::assertSame('100.00', $data['totalGrossAmount']);
        self::assertSame('15.97', $data['totalTaxAmount']);
    }

    public function testFromArrayParsesAllFields(): void
    {
        $voucher = Voucher::fromArray([
            'type'             => 'salesinvoice',
            'voucherNumber'    => '2026100001',
            'voucherDate'      => '2026-02-25',
            'totalGrossAmount' => 29.90,
            'totalTaxAmount'   => 4.77,
            'contactId'        => 'test-uuid-123',
            'remark'           => 'Bestellung #12345',
            'shippingDate'     => '2026-02-26',
            'taxType'          => 'net',
            'voucherItems'     => [
                [
                    'amount'         => 29.90,
                    'taxAmount'      => 4.77,
                    'taxRatePercent' => 19,
                    'categoryId'     => VoucherItem::CATEGORY_EU_SALES,
                ],
            ],
        ]);

        self::assertSame('salesinvoice', $voucher->type);
        self::assertSame('2026100001', $voucher->voucherNumber);
        self::assertSame('2026-02-25', $voucher->voucherDate);
        self::assertSame(29.90, $voucher->totalGrossAmount);
        self::assertSame(4.77, $voucher->totalTaxAmount);
        self::assertSame('test-uuid-123', $voucher->contactId);
        self::assertSame('Bestellung #12345', $voucher->remark);
        self::assertSame('2026-02-26', $voucher->shippingDate);
        self::assertSame('net', $voucher->taxType);
        self::assertCount(1, $voucher->voucherItems);
        self::assertSame(VoucherItem::CATEGORY_EU_SALES, $voucher->voucherItems[0]->categoryId);
    }

    public function testFromArrayUsesDefaultsForMissingFields(): void
    {
        $voucher = Voucher::fromArray([]);

        self::assertSame('', $voucher->type);
        self::assertSame('', $voucher->voucherNumber);
        self::assertSame('', $voucher->voucherDate);
        self::assertSame(0.0, $voucher->totalGrossAmount);
        self::assertSame(0.0, $voucher->totalTaxAmount);
        self::assertSame('', $voucher->contactId);
        self::assertSame('', $voucher->remark);
        self::assertNull($voucher->shippingDate);
        self::assertSame('gross', $voucher->taxType);
        self::assertSame([], $voucher->voucherItems);
    }
}
