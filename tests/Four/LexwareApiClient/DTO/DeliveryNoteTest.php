<?php

declare(strict_types=1);

namespace Four\LexwareApiClient\Tests\DTO;

use Four\LexwareApiClient\DTO\DeliveryNote;
use Four\LexwareApiClient\DTO\InvoiceLineItem;
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

    public function testFromArrayParsesAllFields(): void
    {
        $note = DeliveryNote::fromArray([
            'voucherDate' => '2026-02-25',
            'address'     => [
                'name'        => 'Max Mustermann',
                'street'      => 'Musterstraße 1',
                'city'        => 'Berlin',
                'zip'         => '10115',
                'countryCode' => 'DE',
                'contactId'   => 'contact-uuid-123',
            ],
            'lineItems' => [
                [
                    'name'      => 'Handgemachte Tasse',
                    'quantity'  => 1,
                    'unitName'  => 'Stück',
                    'unitPrice' => [
                        'currency'          => 'EUR',
                        'netAmount'         => 25.21,
                        'taxRatePercentage' => 19.0,
                    ],
                ],
            ],
            'introduction'           => 'Intro',
            'remark'                 => 'Remark',
            'shippingConditions'     => ['shippingDate' => '2026-03-01'],
        ]);

        self::assertSame('2026-02-25', $note->voucherDate);
        self::assertSame('Max Mustermann', $note->addressName);
        self::assertSame('Musterstraße 1', $note->addressStreet);
        self::assertSame('Berlin', $note->addressCity);
        self::assertSame('10115', $note->addressZip);
        self::assertSame('DE', $note->addressCountryCode);
        self::assertCount(1, $note->lineItems);
        self::assertSame('Handgemachte Tasse', $note->lineItems[0]->name);
        self::assertSame('contact-uuid-123', $note->contactId);
        self::assertSame('Intro', $note->introduction);
        self::assertSame('Remark', $note->remark);
        self::assertSame('2026-03-01', $note->shippingConditionsDate);
    }

    public function testFromArrayUsesDefaultsForMissingFields(): void
    {
        $note = DeliveryNote::fromArray([]);

        self::assertSame('', $note->voucherDate);
        self::assertSame('', $note->addressName);
        self::assertSame('', $note->addressStreet);
        self::assertSame('', $note->addressCity);
        self::assertSame('', $note->addressZip);
        self::assertSame('', $note->addressCountryCode);
        self::assertSame([], $note->lineItems);
        self::assertNull($note->contactId);
        self::assertNull($note->introduction);
        self::assertNull($note->remark);
        self::assertNull($note->shippingConditionsDate);
    }

    public function testDeliveryTermsRoundTrip(): void
    {
        $note = new DeliveryNote(voucherDate: '2026-02-25', addressName: 'A', addressStreet: 'S', addressCity: 'C', addressZip: '1', addressCountryCode: 'DE', lineItems: [], deliveryTerms: 'Lieferung frei Haus.');

        self::assertSame('Lieferung frei Haus.', $note->toApiArray()['deliveryTerms']);
        self::assertSame('Lieferung frei Haus.', DeliveryNote::fromArray(['deliveryTerms' => 'Lieferung frei Haus.'])->deliveryTerms);
        self::assertArrayNotHasKey('deliveryTerms', (new DeliveryNote(voucherDate: '2026-02-25', addressName: 'A', addressStreet: 'S', addressCity: 'C', addressZip: '1', addressCountryCode: 'DE', lineItems: []))->toApiArray());
    }
}
