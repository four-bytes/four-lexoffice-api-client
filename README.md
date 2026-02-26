# four-lexoffice-api-client

PHP-Client für die [lexoffice / Lexware Office API v1](https://developers.lexoffice.io/docs/).

Vollständiger Support für alle 20 Endpoints: Articles, Contacts, Invoices, CreditNotes, DeliveryNotes, Quotations, OrderConfirmations, Payments, EventSubscriptions, Vouchers, Files, Voucherlist + Lookup-Endpoints.

Basiert auf `four-bytes/four-http-client` (PSR-18). Rate Limiting und Retry über Middleware — keine direkte cURL-Abhängigkeit.

**API Base URL:** `https://api.lexware.io/v1/`

**Rate Limit:** 2 Requests/Sekunde (Client limitiert auf 1 req/s für Sicherheitsabstand)

---

## Installation

```bash
composer require four-bytes/four-lexoffice-api-client
```

---

## Alle verfügbaren Endpoints

| Endpoint | Klasse | Beschreibung |
|----------|--------|--------------|
| Articles | `ArticleEndpoint` | Artikelverwaltung |
| Contacts | `ContactEndpoint` | Kontakte (Personen & Firmen) |
| Invoices | `InvoiceEndpoint` | Ausgangsrechnungen |
| CreditNotes | `CreditNoteEndpoint` | Gutschriften |
| DeliveryNotes | `DeliveryNoteEndpoint` | Lieferscheine |
| Quotations | `QuotationEndpoint` | Angebote |
| OrderConfirmations | `OrderConfirmationEndpoint` | Auftragsbestätigungen |
| Payments | `PaymentEndpoint` | Zahlungen |
| Vouchers | `VoucherEndpoint` | Buchhaltungsbelege (alt) |
| Voucherlist | `VoucherlistEndpoint` | Belegliste |
| EventSubscriptions | `EventSubscriptionEndpoint` | Webhooks |
| Files | `FileEndpoint` | Datei-Upload/Download |
| **Lookup Endpoints** | | |
| Countries | `CountryEndpoint` | Länderliste |
| Profile | `ProfileEndpoint` | Firmenprofil |
| PaymentConditions | `PaymentConditionEndpoint` | Zahlungsbedingungen |
| PostingCategories | `PostingCategoryEndpoint` | Buchungskategorien |
| PrintLayouts | `PrintLayoutEndpoint` | Drucklayouts |
| RecurringTemplates | `RecurringTemplateEndpoint` | Wiederkehrende Vorlagen |

---

## Client initialisieren

```php
use Four\LexofficeApiClient\LexofficeClient;

// Empfohlen: Static Factory (PSR-18 Client wird automatisch erkannt)
$client = LexofficeClient::create('dein-api-key');

// Alternativ: Constructor mit DI (z.B. für Tests)
$client = new LexofficeClient(
    apiKey: 'dein-api-key',
    httpClient: $psrClient,
    requestFactory: $requestFactory,
    streamFactory: $streamFactory,
);
```

### Endpoints registrieren

```php
use Four\LexofficeApiClient\Endpoint\ContactEndpoint;
use Four\LexofficeApiClient\Endpoint\InvoiceEndpoint;
use Four\LexofficeApiClient\Endpoint\VoucherEndpoint;
use Four\LexofficeApiClient\Endpoint\FileEndpoint;
use Four\LexofficeApiClient\Endpoint\EventSubscriptionEndpoint;

// Alle Endpoints
$contacts    = new ContactEndpoint($client);
$invoices    = new InvoiceEndpoint($client);
$vouchers    = new VoucherEndpoint($client);
$files       = new FileEndpoint($client);
$webhooks    = new EventSubscriptionEndpoint($client);
```

---

## Code-Beispiele

### Kontakt anlegen (Person)

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

### Kontakt anlegen (Firma/Lieferant)

```php
$contact = new Contact(
    isCompany: true,
    companyName: 'Muster GmbH',
    vatId: 'DE123456789',
    street: 'Businessstr. 5',
    zip: '10115',
    city: 'Berlin',
    countryCode: 'DE',
    isVendor: true,   // Firma ist auch Lieferant
);

$contactId = $contacts->create($contact);
```

### Kontakt upsert (create oder update)

```php
// Erstellt neu wenn $existingId null ist, sonst Update
$contactId = $contacts->upsert($contact, existingId: $existingId);
```

### Rechnung erstellen (Invoice)

```php
use Four\LexofficeApiClient\DTO\Invoice;
use Four\LexofficeApiClient\DTO\InvoiceLineItem;

$lineItem = new InvoiceLineItem(
    amount: 29.90,
    taxAmount: 4.77,
    taxRatePercent: 19,
    categoryId: InvoiceLineItem::CATEGORY_INLAND_SALES,
);

$invoice = new Invoice(
    voucherNumber: '2026100001',
    voucherDate: '2026-02-25',
    totalGrossAmount: 29.90,
    totalTaxAmount: 4.77,
    contactId: $contactId,
    remark: 'Bestellung #12345',
    shippingDate: '2026-02-26',
    lineItems: [$lineItem],
);

$invoiceId = $invoices->create($invoice);
```

### Voucher erstellen (alter Weg für Buchhaltungsbelege)

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

### PDF zu Voucher/Rechnung hochladen

```php
$fileId = $files->uploadToVoucher($voucherId, '/pfad/zur/rechnung.pdf');
```

### Eingangsrechnung hochladen (Buchhaltungsbeleg)

```php
$fileId = $files->uploadIncoming('/pfad/zur/eingangsrechnung.pdf');
```

### PDF einer Rechnung downloaden

```php
$pdfContent = $invoices->downloadPdf($invoiceId);

// Oder mit Voucher
$pdfContent = $vouchers->downloadPdf($voucherId);

// Speichern
file_put_contents('/pfad/zur/ausgabe.pdf', $pdfContent);
```

### Webhook registrieren

```php
use Four\LexofficeApiClient\DTO\EventSubscription;

$subscription = new EventSubscription(
    event: 'invoice.created',
    callbackUrl: 'https://meine-domain.de/webhook/lexoffice',
    enabled: true,
);

$webhookId = $webhooks->create($subscription);

// Alle Webhooks auflisten
$allWebhooks = $webhooks->list();

// Webhook löschen
$webhooks->delete($webhookId);
```

---

## Voucher Category-IDs (Buchungskategorien)

| Konstante | UUID | Beschreibung |
|-----------|------|---------------|
| `CATEGORY_INLAND_SALES` | `8f8664a8-fd86-11e1-a21f-0800200c9a66` | Umsatzerlöse Inland (Standard) |
| `CATEGORY_EU_SALES` | `4ebd965a-7126-416c-9d8c-a5c9366ee473` | Umsatzerlöse EU (Privatkunden, OSS) |
| `CATEGORY_EU_DEALER_VAT_FREE` | `9075a4e3-66de-4795-a016-3889feca0d20` | EU-Händler steuerfrei (§4 UStG) |
| `CATEGORY_WORLD_EXPORT` | `93d24c20-ea84-424e-a731-5e1b78d1e6a9` | Exportlieferungen Drittland |
| `CATEGORY_SERVICE_FEES` | `8f8664a7-fd86-11e1-a21f-0800200c9a66` | Dienstleistungserlöse |
| `CATEGORY_REVENUE` | `8f8664a1-fd86-11e1-a21f-0800200c9a66` | Sonstige Erlöse |
| `CATEGORY_SERVICES` | `8f8664a0-fd86-11e1-a21f-0800200c9a66` | Leistungen |
| `CATEGORY_DISTANCE_SALES_EU` | `7c112b66-0565-479c-bc18-5845e080880a` | Fernverkauf EU |
| `CATEGORY_DISTANCE_SALES_EU_TAXABLE` | `4ebd965a-7126-416c-9d8c-a5c9366ee473` | Fernverkauf EU (versteuert) |
| `CATEGORY_THIRD_COUNTRY_SERVICE` | `ef5b1a6e-f690-4004-9a19-91276348894f` | Dienstleistung Drittland |
| `CATEGORY_SMALL_BUSINESS` | `7a1efa0e-6283-4cbf-9583-8e88d3ba5960` | Kleinunternehmer |

---

## Fehlerbehandlung

```php
use Four\LexofficeApiClient\Exception\LexofficeException;
use Four\LexofficeApiClient\Exception\RateLimitException;

try {
    $invoiceId = $invoices->create($invoice);
} catch (RateLimitException $e) {
    // Nach 3 Retries noch HTTP 429
    echo 'Rate Limit überschritten: ' . $e->getMessage();
} catch (LexofficeException $e) {
    echo 'API-Fehler ' . $e->getHttpStatusCode() . ': ' . $e->getMessage();
    echo 'Response: ' . $e->getResponseBody();
}
```

---

## Rate Limiting

Rate Limiting (1 req/s) und Retry-Logik (bis zu 3 Versuche bei HTTP 429) werden automatisch über die Middleware-Stack von `four-bytes/four-http-client` gehandhabt. Kein manuelles `sleep()` oder Throttling nötig.

---

## Lizenz

MIT License - four-bytes
