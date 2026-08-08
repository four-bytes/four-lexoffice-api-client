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
}
