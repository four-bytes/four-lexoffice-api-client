<?php

declare(strict_types=1);

namespace Four\LexofficeApiClient\Tests\DTO;

use Four\LexofficeApiClient\DTO\Contact;
use Four\LexofficeApiClient\DTO\ContactAddress;
use Four\LexofficeApiClient\DTO\ContactCompany;
use Four\LexofficeApiClient\DTO\ContactPerson;
use PHPUnit\Framework\TestCase;

class ContactTest extends TestCase
{
    public function testPersonContactToApiArray(): void
    {
        $contact = new Contact(
            isCustomer: true,
            person: new ContactPerson(lastName: 'Mustermann', firstName: 'Max'),
            billingAddresses: [new ContactAddress(street: 'Musterstr. 1', zip: '12345', city: 'Berlin', countryCode: 'DE')],
            emailsPrivate: ['max@example.com'],
        );

        $data = $contact->toApiArray();

        self::assertArrayHasKey('customer', $data['roles']);
        self::assertArrayNotHasKey('vendor', $data['roles']);
        self::assertArrayHasKey('person', $data);
        self::assertArrayNotHasKey('company', $data);
        self::assertSame('Mustermann', $data['person']['lastName']);
        self::assertSame('Max', $data['person']['firstName']);
        self::assertSame(['max@example.com'], $data['emailAddresses']['private']);
        self::assertCount(1, $data['addresses']['billing']);
        self::assertSame('Musterstr. 1', $data['addresses']['billing'][0]['street']);
    }

    public function testCompanyContactToApiArray(): void
    {
        $contact = new Contact(
            isCustomer: true,
            isVendor: true,
            company: new ContactCompany(name: 'Muster GmbH', vatRegistrationId: 'DE123456789'),
            billingAddresses: [new ContactAddress(street: 'Businessstr. 5', zip: '10115', city: 'Berlin', countryCode: 'DE')],
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

    public function testCompanyWithContactPerson(): void
    {
        $contact = new Contact(
            isCustomer: true,
            company: new ContactCompany(
                name: 'Tech GmbH',
                contactPersons: [new ContactPerson(lastName: 'Schmidt', firstName: 'Anna', primary: true)],
            ),
        );

        $data = $contact->toApiArray();

        self::assertCount(1, $data['company']['contactPersons']);
        self::assertSame('Schmidt', $data['company']['contactPersons'][0]['lastName']);
    }

    public function testNoteIncluded(): void
    {
        $contact = new Contact(isCustomer: true, person: new ContactPerson(lastName: 'Test'), note: 'Wichtiger Kunde');
        $data = $contact->toApiArray();
        self::assertSame('Wichtiger Kunde', $data['note']);
    }

    public function testNoteOmittedWhenEmpty(): void
    {
        $contact = new Contact(isCustomer: true, person: new ContactPerson(lastName: 'Test'));
        $data = $contact->toApiArray();
        self::assertArrayNotHasKey('note', $data);
    }

    public function testFromArrayPerson(): void
    {
        $apiResponse = [
            'id' => 'abc-123',
            'version' => 2,
            'roles' => ['customer' => ['number' => 10001]],
            'person' => ['firstName' => 'Inge', 'lastName' => 'Musterfrau', 'salutation' => 'Frau'],
            'archived' => false,
        ];

        $contact = Contact::fromArray($apiResponse);

        self::assertSame('abc-123', $contact->id);
        self::assertSame(2, $contact->version);
        self::assertTrue($contact->isCustomer);
        self::assertFalse($contact->isVendor);
        self::assertNotNull($contact->person);
        self::assertSame('Inge', $contact->person->firstName);
        self::assertSame('Musterfrau', $contact->person->lastName);
    }

    public function testFromArrayCompany(): void
    {
        $apiResponse = [
            'id' => 'xyz-456',
            'version' => 1,
            'roles' => ['customer' => ['number' => 10307], 'vendor' => ['number' => 70303]],
            'company' => [
                'name' => 'Testfirma',
                'vatRegistrationId' => 'DE123456789',
                'contactPersons' => [
                    ['firstName' => 'Max', 'lastName' => 'Mustermann', 'primary' => true],
                ],
            ],
            'addresses' => [
                'billing' => [['street' => 'Hauptstr. 5', 'zip' => '12345', 'city' => 'Musterort', 'countryCode' => 'DE']],
            ],
            'emailAddresses' => ['business' => ['business@lexware.de']],
            'note' => 'Notizen',
            'archived' => false,
        ];

        $contact = Contact::fromArray($apiResponse);

        self::assertTrue($contact->isCustomer);
        self::assertTrue($contact->isVendor);
        self::assertNotNull($contact->company);
        self::assertSame('Testfirma', $contact->company->name);
        self::assertCount(1, $contact->company->contactPersons);
        self::assertSame('Max', $contact->company->contactPersons[0]->firstName);
        self::assertCount(1, $contact->billingAddresses);
        self::assertSame('Hauptstr. 5', $contact->billingAddresses[0]->street);
        self::assertSame(['business@lexware.de'], $contact->emailsBusiness);
        self::assertSame('Notizen', $contact->note);
    }

    public function testXRechnungInApiArray(): void
    {
        $contact = new Contact(
            isCustomer: true,
            person: new ContactPerson(lastName: 'Behörde'),
            xRechnungBuyerReference: '04011000-1234512345-35',
            xRechnungVendorNumber: '70123456',
        );

        $data = $contact->toApiArray();

        self::assertSame('04011000-1234512345-35', $data['xRechnung']['buyerReference']);
        self::assertSame('70123456', $data['xRechnung']['vendorNumberAtCustomer']);
    }
}
