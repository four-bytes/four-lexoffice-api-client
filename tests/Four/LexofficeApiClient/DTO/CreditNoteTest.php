<?php

declare(strict_types=1);

namespace Four\LexofficeApiClient\Tests\DTO;

use Four\LexofficeApiClient\DTO\CreditNote;
use Four\LexofficeApiClient\DTO\InvoiceLineItem;
use PHPUnit\Framework\TestCase;

class CreditNoteTest extends TestCase
{
    private function buildCreditNote(
        ?string $contactId = null,
        ?string $introduction = null,
        ?string $remark = null,
        ?string $paymentConditionId = null,
        ?string $shippingConditionsDate = null,
    ): CreditNote {
        return new CreditNote(
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
        $data = $this->buildCreditNote()->toApiArray();

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
        $data = $this->buildCreditNote(
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
        $data = $this->buildCreditNote()->toApiArray();

        self::assertArrayNotHasKey('contactId', $data['address']);
        self::assertArrayNotHasKey('introduction', $data);
        self::assertArrayNotHasKey('remark', $data);
        self::assertArrayNotHasKey('paymentConditions', $data);
        self::assertArrayNotHasKey('shippingConditions', $data);
    }

    public function testFromArrayParsesAllFields(): void
    {
        $creditNote = CreditNote::fromArray([
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
            'taxConditions'      => ['taxType' => 'net'],
            'introduction'       => 'Einleitung',
            'remark'             => 'Bemerkung',
            'paymentConditions'  => ['id' => 'payment-1'],
            'shippingConditions' => ['shippingDate' => '2026-03-01'],
        ]);

        self::assertSame('2026-02-25', $creditNote->voucherDate);
        self::assertSame('Max Mustermann', $creditNote->addressName);
        self::assertSame('Musterstraße 1', $creditNote->addressStreet);
        self::assertSame('Berlin', $creditNote->addressCity);
        self::assertSame('10115', $creditNote->addressZip);
        self::assertSame('DE', $creditNote->addressCountryCode);
        self::assertCount(1, $creditNote->lineItems);
        self::assertSame('Handgemachte Tasse', $creditNote->lineItems[0]->name);
        self::assertSame('net', $creditNote->taxType);
        self::assertSame('contact-uuid-123', $creditNote->contactId);
        self::assertSame('Einleitung', $creditNote->introduction);
        self::assertSame('Bemerkung', $creditNote->remark);
        self::assertSame('payment-1', $creditNote->paymentConditionId);
        self::assertSame('2026-03-01', $creditNote->shippingConditionsDate);
    }

    public function testFromArrayUsesDefaultsForMissingFields(): void
    {
        $creditNote = CreditNote::fromArray([]);

        self::assertSame('', $creditNote->voucherDate);
        self::assertSame('', $creditNote->addressName);
        self::assertSame('', $creditNote->addressStreet);
        self::assertSame('', $creditNote->addressCity);
        self::assertSame('', $creditNote->addressZip);
        self::assertSame('', $creditNote->addressCountryCode);
        self::assertSame([], $creditNote->lineItems);
        self::assertSame('gross', $creditNote->taxType);
        self::assertNull($creditNote->contactId);
        self::assertNull($creditNote->introduction);
        self::assertNull($creditNote->remark);
        self::assertNull($creditNote->paymentConditionId);
        self::assertNull($creditNote->shippingConditionsDate);
    }
}
