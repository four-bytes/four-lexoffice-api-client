<?php

declare(strict_types=1);

namespace Four\LexwareApiClient\Tests\DTO;

use Four\LexwareApiClient\DTO\Dunning;
use Four\LexwareApiClient\DTO\InvoiceLineItem;
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

    public function testFromArrayParsesAllFields(): void
    {
        $dunning = Dunning::fromArray([
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
            'taxConditions' => ['taxType' => 'net'],
            'introduction'  => 'Intro',
            'remark'        => 'Remark',
        ]);

        self::assertSame('2026-02-25', $dunning->voucherDate);
        self::assertSame('Max Mustermann', $dunning->addressName);
        self::assertSame('Musterstraße 1', $dunning->addressStreet);
        self::assertSame('Berlin', $dunning->addressCity);
        self::assertSame('10115', $dunning->addressZip);
        self::assertSame('DE', $dunning->addressCountryCode);
        self::assertCount(1, $dunning->lineItems);
        self::assertSame('net', $dunning->taxType);
        self::assertSame('contact-uuid-123', $dunning->contactId);
        self::assertSame('Intro', $dunning->introduction);
        self::assertSame('Remark', $dunning->remark);
    }

    public function testFromArrayUsesDefaultsForMissingFields(): void
    {
        $dunning = Dunning::fromArray([]);

        self::assertSame('', $dunning->voucherDate);
        self::assertSame('', $dunning->addressName);
        self::assertSame('', $dunning->addressStreet);
        self::assertSame('', $dunning->addressCity);
        self::assertSame('', $dunning->addressZip);
        self::assertSame('', $dunning->addressCountryCode);
        self::assertSame([], $dunning->lineItems);
        self::assertSame('gross', $dunning->taxType);
        self::assertNull($dunning->contactId);
        self::assertNull($dunning->introduction);
        self::assertNull($dunning->remark);
    }
}
