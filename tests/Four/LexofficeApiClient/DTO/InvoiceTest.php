<?php

declare(strict_types=1);

namespace Four\LexofficeApiClient\Tests\DTO;

use Four\LexofficeApiClient\DTO\Invoice;
use Four\LexofficeApiClient\DTO\InvoiceLineItem;
use PHPUnit\Framework\TestCase;

class InvoiceTest extends TestCase
{
    private function buildInvoice(
        ?string $contactId = null,
        ?string $remark = null,
        ?string $addressSupplement = null,
    ): Invoice {
        return new Invoice(
            voucherDate:        '2026-02-25',
            addressName:        'Max Mustermann',
            addressStreet:      'Musterstraße 1',
            addressCity:        'Berlin',
            addressZip:         '10115',
            addressCountryCode: 'DE',
            lineItems:          [
                new InvoiceLineItem(
                    name:              'Handgemachte Tasse',
                    quantity:          1.0,
                    unitName:          'Stück',
                    unitNetPrice:      25.21,
                    taxRatePercentage: 19.0,
                ),
            ],
            taxType:   'gross',
            contactId: $contactId,
            remark:    $remark,
            addressSupplement: $addressSupplement,
        );
    }

    public function testToApiArrayContainsRequiredFields(): void
    {
        $invoice = $this->buildInvoice();
        $data    = $invoice->toApiArray();

        self::assertSame('2026-02-25', $data['voucherDate']);
        self::assertSame('Max Mustermann', $data['address']['name']);
        self::assertSame('Musterstraße 1', $data['address']['street']);
        self::assertSame('Berlin', $data['address']['city']);
        self::assertSame('10115', $data['address']['zip']);
        self::assertSame('DE', $data['address']['countryCode']);
        self::assertSame('gross', $data['taxConditions']['taxType']);
        self::assertCount(1, $data['lineItems']);
    }

    public function testToApiArrayOmitsNullContactId(): void
    {
        $invoice = $this->buildInvoice(contactId: null);
        $data    = $invoice->toApiArray();

        self::assertArrayNotHasKey('contactId', $data['address']);
    }

    public function testToApiArrayIncludesContactId(): void
    {
        $invoice = $this->buildInvoice(contactId: 'contact-uuid-123');
        $data    = $invoice->toApiArray();

        self::assertSame('contact-uuid-123', $data['address']['contactId']);
    }

    public function testToApiArrayIncludesRemark(): void
    {
        $invoice = $this->buildInvoice(remark: 'Vielen Dank für Ihre Bestellung!');
        $data    = $invoice->toApiArray();

        self::assertSame('Vielen Dank für Ihre Bestellung!', $data['remark']);
    }

    public function testToApiArrayOmitsNullRemark(): void
    {
        $invoice = $this->buildInvoice(remark: null);
        $data    = $invoice->toApiArray();

        self::assertArrayNotHasKey('remark', $data);
    }

    public function testToApiArrayIncludesAddressSupplement(): void
    {
        $invoice = $this->buildInvoice(addressSupplement: 'c/o Four Bytes GmbH');
        $data    = $invoice->toApiArray();

        self::assertSame('c/o Four Bytes GmbH', $data['address']['supplement']);
    }

    public function testToApiArrayOmitsNullAddressSupplement(): void
    {
        $invoice = $this->buildInvoice(addressSupplement: null);
        $data    = $invoice->toApiArray();

        self::assertArrayNotHasKey('supplement', $data['address']);
    }

    public function testFromArrayParsesAllFields(): void
    {
        $invoice = Invoice::fromArray([
            'voucherDate' => '2026-02-25',
            'address'     => [
                'name'        => 'Max Mustermann',
                'street'      => 'Musterstraße 1',
                'city'        => 'Berlin',
                'zip'         => '10115',
                'countryCode' => 'DE',
                'contactId'   => 'contact-uuid-123',
                'supplement'  => 'c/o Four Bytes GmbH',
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
            'introduction'       => 'Intro',
            'remark'             => 'Remark',
            'paymentConditions'  => ['id' => 'payment-1'],
            'shippingConditions' => ['shippingDate' => '2026-03-01'],
            'totalPrice'         => ['totalGrossAmount' => 30.0],
            'title'              => 'Rechnung',
            'xRechnung'          => ['buyerReference' => 'ref-1'],
            'printLayoutId'      => 'layout-1',
        ]);

        self::assertSame('2026-02-25', $invoice->voucherDate);
        self::assertSame('Max Mustermann', $invoice->addressName);
        self::assertSame('Musterstraße 1', $invoice->addressStreet);
        self::assertSame('Berlin', $invoice->addressCity);
        self::assertSame('10115', $invoice->addressZip);
        self::assertSame('DE', $invoice->addressCountryCode);
        self::assertCount(1, $invoice->lineItems);
        self::assertSame('Handgemachte Tasse', $invoice->lineItems[0]->name);
        self::assertSame('net', $invoice->taxType);
        self::assertSame('contact-uuid-123', $invoice->contactId);
        self::assertSame('Intro', $invoice->introduction);
        self::assertSame('Remark', $invoice->remark);
        self::assertSame('payment-1', $invoice->paymentConditionId);
        self::assertSame('2026-03-01', $invoice->shippingConditionsDate);
        self::assertSame(['totalGrossAmount' => 30.0], $invoice->totalPrice);
        self::assertSame('Rechnung', $invoice->title);
        self::assertSame(['buyerReference' => 'ref-1'], $invoice->xRechnung);
        self::assertSame('layout-1', $invoice->printLayoutId);
        self::assertSame('c/o Four Bytes GmbH', $invoice->addressSupplement);
    }

    public function testFromArrayUsesDefaultsForMissingFields(): void
    {
        $invoice = Invoice::fromArray([]);

        self::assertSame('', $invoice->voucherDate);
        self::assertSame('', $invoice->addressName);
        self::assertSame('', $invoice->addressStreet);
        self::assertSame('', $invoice->addressCity);
        self::assertSame('', $invoice->addressZip);
        self::assertSame('', $invoice->addressCountryCode);
        self::assertSame([], $invoice->lineItems);
        self::assertSame('gross', $invoice->taxType);
        self::assertNull($invoice->contactId);
        self::assertNull($invoice->introduction);
        self::assertNull($invoice->remark);
        self::assertNull($invoice->paymentConditionId);
        self::assertNull($invoice->shippingConditionsDate);
        self::assertNull($invoice->totalPrice);
        self::assertNull($invoice->title);
        self::assertNull($invoice->xRechnung);
        self::assertNull($invoice->printLayoutId);
        self::assertNull($invoice->addressSupplement);
    }
}
