<?php

declare(strict_types=1);

namespace Four\LexofficeApiClient\Tests\DTO;

use Four\LexofficeApiClient\DTO\Contact;
use PHPUnit\Framework\TestCase;

class ContactTest extends TestCase
{
    public function testPersonToApiArray(): void
    {
        $contact = new Contact(
            firstName: 'Max',
            lastName: 'Mustermann',
            email: 'max@example.com',
            street: 'Musterstr. 1',
            zip: '12345',
            city: 'Berlin',
            countryCode: 'DE',
        );

        $data = $contact->toApiArray();

        self::assertSame(0, $data['version']);
        self::assertArrayHasKey('customer', $data['roles']);
        self::assertArrayNotHasKey('vendor', $data['roles']);
        self::assertArrayHasKey('person', $data);
        self::assertArrayNotHasKey('company', $data);
        self::assertSame('Max', $data['person']['firstName']);
        self::assertSame('Mustermann', $data['person']['lastName']);
        self::assertSame('Max Mustermann', $data['person']['name']);
        self::assertSame([['street' => 'Musterstr. 1', 'zip' => '12345', 'city' => 'Berlin', 'countryCode' => 'DE']], $data['addresses']['billing']);
        self::assertSame(['private' => ['max@example.com']], $data['emailAddresses']);
    }

    public function testCompanyToApiArray(): void
    {
        $contact = new Contact(
            isCompany: true,
            companyName: 'Muster GmbH',
            vatId: 'DE123456789',
            street: 'Businessstr. 5',
            zip: '10115',
            city: 'Berlin',
            countryCode: 'DE',
            isVendor: true,
        );

        $data = $contact->toApiArray();

        self::assertArrayHasKey('customer', $data['roles']);
        self::assertArrayHasKey('vendor', $data['roles']);
        self::assertArrayHasKey('company', $data);
        self::assertArrayNotHasKey('person', $data);
        self::assertSame('Muster GmbH', $data['company']['name']);
        self::assertSame('DE123456789', $data['company']['vatRegistrationId']);
        self::assertArrayNotHasKey('emailAddresses', $data);
    }

    public function testSupplementIncludedWhenSet(): void
    {
        $contact = new Contact(
            firstName: 'Anna',
            lastName: 'Müller',
            street: 'Hauptstr. 1',
            supplement: 'Hinterhaus',
            zip: '80331',
            city: 'München',
        );

        $data = $contact->toApiArray();
        self::assertSame('Hinterhaus', $data['addresses']['billing'][0]['supplement']);
    }

    public function testSupplementOmittedWhenEmpty(): void
    {
        $contact = new Contact(
            firstName: 'Hans',
            lastName: 'Schmidt',
            street: 'Teststr. 1',
            zip: '20095',
            city: 'Hamburg',
        );

        $data = $contact->toApiArray();
        self::assertArrayNotHasKey('supplement', $data['addresses']['billing'][0]);
    }

    public function testEmailOmittedWhenEmpty(): void
    {
        $contact = new Contact(
            isCompany: true,
            companyName: 'Firma ohne Mail',
        );

        $data = $contact->toApiArray();
        self::assertArrayNotHasKey('emailAddresses', $data);
    }

    public function testReadonlyProperties(): void
    {
        $contact = new Contact(
            id: 'abc-123',
            version: 2,
            firstName: 'Test',
        );

        self::assertSame('abc-123', $contact->id);
        self::assertSame(2, $contact->version);
        self::assertSame('Test', $contact->firstName);
    }
}
