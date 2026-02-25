# four-lexoffice-api-client

PHP-Client für die [lexoffice / Lexware Office API v1](https://developers.lexoffice.io/docs/).

Unterstützt Kontakte (Personen & Firmen), Ausgangsrechnungen (Voucher) und PDF-Datei-Uploads.
Kein Framework, keine externen Dependencies — nur PHP 8.2+ und cURL.

## Installation

```bash
composer require four-bytes/four-lexoffice-api-client
```

## Verwendung

### Client initialisieren

```php
use Four\LexofficeApiClient\LexofficeClient;
use Four\LexofficeApiClient\Endpoint\ContactEndpoint;
use Four\LexofficeApiClient\Endpoint\VoucherEndpoint;
use Four\LexofficeApiClient\Endpoint\FileEndpoint;

$client = new LexofficeClient(apiKey: 'dein-api-key');

$contacts = new ContactEndpoint($client);
$vouchers = new VoucherEndpoint($client);
$files    = new FileEndpoint($client);
```

### Contact erstellen (Person)

```php
use Four\LexofficeApiClient\DTO\Contact;

$contact = new Contact(
    firstName: 'Max',
    lastName: 'Mustermann',
    email: 'max@example.com',
    street: 'Musterstr. 1',
    zip: '12345',
    city: 'Berlin',
    countryCode: 'DE',
);

$contactId = $contacts->create($contact);
```

### Contact erstellen (Firma/Händler)

```php
$contact = new Contact(
    isCompany: true,
    companyName: 'Muster GmbH',
    vatId: 'DE123456789',
    street: 'Businessstr. 5',
    zip: '10115',
    city: 'Berlin',
    isVendor: true,   // Firma ist auch Lieferant
);

$contactId = $contacts->create($contact);
```

### Contact upsert (create oder update)

```php
// Erstellt neu wenn $existingId null ist, sonst Update
$contactId = $contacts->upsert($contact, existingId: $existingId);
```

### Ausgangsrechnung (Voucher) erstellen

```php
use Four\LexofficeApiClient\DTO\Voucher;
use Four\LexofficeApiClient\DTO\VoucherItem;

$item = new VoucherItem(
    amount: 29.90,
    taxAmount: 4.77,
    taxRatePercent: 19,
    categoryId: VoucherItem::CATEGORY_INLAND_SALES,
);

$voucher = new Voucher(
    type: 'salesinvoice',
    voucherNumber: '2026100001',
    voucherDate: '2026-02-25',
    totalGrossAmount: 29.90,
    totalTaxAmount: 4.77,
    contactId: $contactId,
    remark: 'Bestellung #12345',
    shippingDate: '2026-02-26',
    voucherItems: [$item],
);

$voucherId = $vouchers->create($voucher);
```

### PDF zu Rechnung hochladen

```php
$fileId = $files->uploadToVoucher($voucherId, '/pfad/zur/rechnung.pdf');
```

### Eingangsrechnung hochladen

```php
$fileId = $files->uploadIncoming('/pfad/zur/eingangsrechnung.pdf');
```

### Fehlerbehandlung

```php
use Four\LexofficeApiClient\Exception\LexofficeException;
use Four\LexofficeApiClient\Exception\RateLimitException;

try {
    $voucherId = $vouchers->create($voucher);
} catch (RateLimitException $e) {
    // Nach 3 Retries noch HTTP 429
    echo 'Rate Limit überschritten: ' . $e->getMessage();
} catch (LexofficeException $e) {
    echo 'API-Fehler ' . $e->getHttpStatusCode() . ': ' . $e->getMessage();
    echo 'Response: ' . $e->getResponseBody();
}
```

## Bekannte Kategorie-IDs (VoucherItem)

| Konstante                        | UUID                                   | Beschreibung                         |
|----------------------------------|----------------------------------------|--------------------------------------|
| `CATEGORY_INLAND_SALES`          | `8f8664a8-fd86-11e1-a21f-0800200c9a66` | Umsatzerlöse Inland (Standard)       |
| `CATEGORY_EU_SALES`              | `4ebd965a-7126-416c-9d8c-a5c9366ee473` | Umsatzerlöse EU (Privatkunden, OSS)  |
| `CATEGORY_EU_DEALER_VAT_FREE`    | `9075a4e3-66de-4795-a016-3889feca0d20` | EU-Händler steuerfrei (§4 UStG)      |
| `CATEGORY_WORLD_EXPORT`          | `93d24c20-ea84-424e-a731-5e1b78d1e6a9` | Exportlieferungen Drittland          |
| `CATEGORY_SERVICE_FEES`          | `8f8664a7-fd86-11e1-a21f-0800200c9a66` | Dienstleistungserlöse                |
| `CATEGORY_REVENUE`               | `8f8664a1-fd86-11e1-a21f-0800200c9a66` | Sonstige Erlöse                      |

## Rate Limiting

Der Client wartet automatisch mindestens 1000ms zwischen Requests (lexoffice-Limit: 2 req/s, wir limitieren auf 1 req/s für Sicherheitsabstand). Bei HTTP 429 werden bis zu 3 Retries mit je 1s Wartezeit durchgeführt.
