<?php

declare(strict_types=1);

namespace Four\LexwareApiClient\Tests\DTO;

use Four\LexwareApiClient\DTO\InvoiceLineItem;
use Four\LexwareApiClient\DTO\Quotation;
use PHPUnit\Framework\TestCase;

class QuotationTest extends TestCase
{
    private function buildQuotation(
        ?string $contactId = null,
        ?string $introduction = null,
        ?string $remark = null,
        ?string $paymentConditionId = null,
        ?string $shippingConditionsDate = null,
    ): Quotation {
        return new Quotation(
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
        $data = $this->buildQuotation()->toApiArray();

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
        $data = $this->buildQuotation(
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
        $data = $this->buildQuotation()->toApiArray();

        self::assertArrayNotHasKey('contactId', $data['address']);
        self::assertArrayNotHasKey('introduction', $data);
        self::assertArrayNotHasKey('remark', $data);
        self::assertArrayNotHasKey('paymentConditions', $data);
        self::assertArrayNotHasKey('shippingConditions', $data);
    }

    public function testFromArrayParsesAllFields(): void
    {
        $quotation = Quotation::fromArray([
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

        self::assertSame('2026-02-25', $quotation->voucherDate);
        self::assertSame('Max Mustermann', $quotation->addressName);
        self::assertSame('Musterstraße 1', $quotation->addressStreet);
        self::assertSame('Berlin', $quotation->addressCity);
        self::assertSame('10115', $quotation->addressZip);
        self::assertSame('DE', $quotation->addressCountryCode);
        self::assertCount(1, $quotation->lineItems);
        self::assertSame('Handgemachte Tasse', $quotation->lineItems[0]->name);
        self::assertSame('net', $quotation->taxType);
        self::assertSame('contact-uuid-123', $quotation->contactId);
        self::assertSame('Einleitung', $quotation->introduction);
        self::assertSame('Bemerkung', $quotation->remark);
        self::assertSame('payment-1', $quotation->paymentConditionId);
        self::assertSame('2026-03-01', $quotation->shippingConditionsDate);
    }

    public function testFromArrayUsesDefaultsForMissingFields(): void
    {
        $quotation = Quotation::fromArray([]);

        self::assertSame('', $quotation->voucherDate);
        self::assertSame('', $quotation->addressName);
        self::assertSame('', $quotation->addressStreet);
        self::assertSame('', $quotation->addressCity);
        self::assertSame('', $quotation->addressZip);
        self::assertSame('', $quotation->addressCountryCode);
        self::assertSame([], $quotation->lineItems);
        self::assertSame('gross', $quotation->taxType);
        self::assertNull($quotation->contactId);
        self::assertNull($quotation->introduction);
        self::assertNull($quotation->remark);
        self::assertNull($quotation->paymentConditionId);
        self::assertNull($quotation->shippingConditionsDate);
    }
}
