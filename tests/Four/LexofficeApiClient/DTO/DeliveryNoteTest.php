<?php

declare(strict_types=1);

namespace Four\LexofficeApiClient\Tests\DTO;

use Four\LexofficeApiClient\DTO\DeliveryNote;
use Four\LexofficeApiClient\DTO\InvoiceLineItem;
use PHPUnit\Framework\TestCase;

class DeliveryNoteTest extends TestCase
{
    public function testToApiArrayHasNoTaxOrPaymentConditions(): void
    {
        $note = new DeliveryNote(
            voucherDate:        '2026-02-25',
            addressName:        'Erika Mustermann',
            addressStreet:      'Hauptstraße 5',
            addressCity:        'München',
            addressZip:         '80331',
            addressCountryCode: 'DE',
            lineItems:          [
                new InvoiceLineItem(
                    name:              'Artikel',
                    quantity:          3.0,
                    unitName:          'Stück',
                    unitNetPrice:      10.00,
                    taxRatePercentage: 19.0,
                ),
            ],
        );

        $data = $note->toApiArray();

        self::assertArrayNotHasKey('taxConditions', $data);
        self::assertArrayNotHasKey('paymentConditions', $data);
        self::assertSame('2026-02-25', $data['voucherDate']);
        self::assertSame('Erika Mustermann', $data['address']['name']);
        self::assertCount(1, $data['lineItems']);
    }

    public function testToApiArrayIncludesShippingConditions(): void
    {
        $note = new DeliveryNote(
            voucherDate:            '2026-02-25',
            addressName:            'Test GmbH',
            addressStreet:          'Testweg 1',
            addressCity:            'Hamburg',
            addressZip:             '20095',
            addressCountryCode:     'DE',
            lineItems:              [],
            shippingConditionsDate: '2026-03-01',
        );

        $data = $note->toApiArray();

        self::assertSame('2026-03-01', $data['shippingConditions']['shippingDate']);
    }
}
