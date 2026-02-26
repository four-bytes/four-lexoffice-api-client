<?php

declare(strict_types=1);

namespace Four\LexofficeApiClient\Tests\DTO;

use Four\LexofficeApiClient\DTO\Dunning;
use Four\LexofficeApiClient\DTO\InvoiceLineItem;
use PHPUnit\Framework\TestCase;

class DunningTest extends TestCase
{
    private function buildDunning(?string $contactId = null, ?string $remark = null): Dunning
    {
        return new Dunning(
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
        );
    }

    public function testToApiArrayContainsRequiredFields(): void
    {
        $dunning = $this->buildDunning();
        $data    = $dunning->toApiArray();

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
        $dunning = $this->buildDunning(contactId: null);
        $data    = $dunning->toApiArray();

        self::assertArrayNotHasKey('contactId', $data['address']);
    }

    public function testToApiArrayIncludesContactId(): void
    {
        $dunning = $this->buildDunning(contactId: 'contact-uuid-123');
        $data    = $dunning->toApiArray();

        self::assertSame('contact-uuid-123', $data['address']['contactId']);
    }

    public function testToApiArrayIncludesRemark(): void
    {
        $dunning = $this->buildDunning(remark: 'Vielen Dank für Ihre Bestellung!');
        $data    = $dunning->toApiArray();

        self::assertSame('Vielen Dank für Ihre Bestellung!', $data['remark']);
    }

    public function testToApiArrayOmitsNullRemark(): void
    {
        $dunning = $this->buildDunning(remark: null);
        $data    = $dunning->toApiArray();

        self::assertArrayNotHasKey('remark', $data);
    }
}
