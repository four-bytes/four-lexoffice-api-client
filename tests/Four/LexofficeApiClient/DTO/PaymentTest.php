<?php

declare(strict_types=1);

namespace Four\LexofficeApiClient\Tests\DTO;

use Four\LexofficeApiClient\DTO\Payment;
use PHPUnit\Framework\TestCase;

class PaymentTest extends TestCase
{
    public function testFromArrayMapsAllFields(): void
    {
        $data = [
            'openAmount'    => 29.99,
            'paymentStatus' => 'openRevenue',
            'voucherType'   => 'invoice',
            'voucherStatus' => 'open',
            'paymentItems'  => [
                ['amount' => 29.99, 'paymentItemType' => 'payment'],
            ],
        ];

        $payment = Payment::fromArray($data);

        self::assertSame(29.99, $payment->openAmount);
        self::assertSame('openRevenue', $payment->paymentStatus);
        self::assertSame('invoice', $payment->voucherType);
        self::assertSame('open', $payment->voucherStatus);
        self::assertCount(1, $payment->paymentItems);
    }

    public function testFromArrayHandlesMissingFields(): void
    {
        $payment = Payment::fromArray([]);

        self::assertSame(0.0, $payment->openAmount);
        self::assertSame('', $payment->paymentStatus);
        self::assertSame('', $payment->voucherType);
        self::assertSame('', $payment->voucherStatus);
        self::assertSame([], $payment->paymentItems);
    }

    public function testStatusConstants(): void
    {
        self::assertSame('balanced', Payment::STATUS_BALANCED);
        self::assertSame('openRevenue', Payment::STATUS_OPEN_REVENUE);
        self::assertSame('openExpense', Payment::STATUS_OPEN_EXPENSE);
    }
}
