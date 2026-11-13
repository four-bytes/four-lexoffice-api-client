<?php

declare(strict_types=1);

namespace Four\LexofficeApiClient\Tests\DTO;

use Four\LexofficeApiClient\DTO\InvoiceLineItem;
use Four\LexofficeApiClient\DTO\OrderConfirmation;
use PHPUnit\Framework\TestCase;

class OrderConfirmationTest extends TestCase
{
    private function buildOrderConfirmation(
        ?string $contactId = null,
        ?string $introduction = null,
        ?string $remark = null,
        ?string $paymentConditionId = null,
        ?string $shippingConditionsDate = null,
    ): OrderConfirmation {
        return new OrderConfirmation(
            voucherDate:            '2026-02-25',
            addressName:            'Max Mustermann',
            addressStreet:          'Musterstraße 1',
            addressCity:            'Berlin',
            addressZip:             '10115',
            addressCountryCode:     'DE',
            lineItems:              [
                new InvoiceLineItem(
                    name:              'Handgemachte Tasse',
                    quantity:          1.0,
                    unitName:          'Stück',
                    unitNetPrice:      25.21,
                    taxRatePercentage: 19.0,
                ),
            ],
            taxType:                'gross',
            contactId:              $contactId,
            introduction:           $introduction,
            remark:                 $remark,
            paymentConditionId:     $paymentConditionId,
            shippingConditionsDate: $shippingConditionsDate,
        );
    }

    public function testToApiArrayContainsRequiredFields(): void
    {
        $data = $this->buildOrderConfirmation()->toApiArray();

        self::assertSame('2026-02-25', $data['voucherDate']);
        self::assertSame('Max Mustermann', $data['address']['name']);
        self::assertSame('Musterstraße 1', $data['address']['street']);
        self::assertSame('Berlin', $data['address']['city']);
        self::assertSame('10115', $data['address']['zip']);
        self::assertSame('DE', $data['address']['countryCode']);
        self::assertSame('gross', $data['taxConditions']['taxType']);
        self::assertCount(1, $data['lineItems']);
    }

    public function testToApiArrayIncludesOptionalFields(): void
    {
        $data = $this->buildOrderConfirmation(
            contactId:              'contact-uuid-123',
            introduction:           'Einleitung',
            remark:                 'Bemerkung',
            paymentConditionId:     'payment-1',
            shippingConditionsDate: '2026-03-01',
        )->toApiArray();

        self::assertSame('contact-uuid-123', $data['address']['contactId']);
        self::assertSame('Einleitung', $data['introduction']);
        self::assertSame('Bemerkung', $data['remark']);
        self::assertSame('payment-1', $data['paymentConditions']['id']);
        self::assertSame('2026-03-01', $data['shippingConditions']['shippingDate']);
        self::assertSame('delivery', $data['shippingConditions']['shippingType']);
    }

    public function testToApiArrayOmitsNullOptionalFields(): void
    {
        $data = $this->buildOrderConfirmation()->toApiArray();

        self::assertArrayNotHasKey('contactId', $data['address']);
        self::assertArrayNotHasKey('introduction', $data);
        self::assertArrayNotHasKey('remark', $data);
        self::assertArrayNotHasKey('paymentConditions', $data);
        self::assertArrayNotHasKey('shippingConditions', $data);
    }
}
