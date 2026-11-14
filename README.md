# four-lexware-api-client

PHP 8.4+ client for the [Lexware Office API](https://developers.lexware.io/docs/) (formerly lexoffice) — create and manage contacts, invoices, vouchers and files from your PHP application, with rate limiting and retry built in.

[![Packagist Version](https://img.shields.io/packagist/v/four-bytes/four-lexware-api-client)](https://packagist.org/packages/four-bytes/four-lexware-api-client)
[![PHP Version](https://img.shields.io/packagist/dependency-v/four-bytes/four-lexware-api-client/php)](https://packagist.org/packages/four-bytes/four-lexware-api-client)
[![License](https://img.shields.io/packagist/l/four-bytes/four-lexware-api-client)](LICENSE)
[![Downloads](https://img.shields.io/packagist/dt/four-bytes/four-lexware-api-client)](https://packagist.org/packages/four-bytes/four-lexware-api-client)

> **Disclaimer:** this is an unofficial, community-maintained client. It is not affiliated with, endorsed by, or sponsored by Lexware GmbH & Co. KG. The [official API documentation](https://developers.lexware.io/docs/) is the reference for all request and response fields.

The API was branded "lexoffice" until it was rebranded to Lexware on 2025-05-26. API host: `https://api.lexware.io/v1/`, web app: `https://app.lexware.de`.

## Features

- All 20 endpoint groups of the Lexware Office API v1 — see the [coverage table](#api-coverage)
- Typed DTOs for requests (`Invoice`, `Contact`, `Voucher`, …) with `toApiArray()` / `fromArray()`
- Sales voucher downloads as PDF or XRechnung XML, with content type and file name (`DownloadedFile`)
- E-invoicing fields: `xRechnung.buyerReference`, `electronicDocumentProfile` (ZUGFeRD / XRechnung)
- Rate limiting (1 req/s, the API allows 2) and retry on HTTP 429 via middleware
- PSR-18 / PSR-17 based — bring your own HTTP client or use the bundled factory

## Requirements

- PHP ^8.4 with `ext-json`
- A PSR-18 HTTP client and PSR-17 factories — `LexwareClient::create()` wires them up via [`four-bytes/four-http-client`](https://github.com/four-bytes/four-http-client) and `nyholm/psr7`; with the constructor you can pass your own (e.g. `symfony/http-client`, `guzzlehttp/guzzle`)
- A Lexware Office API key ([create one here](https://app.lexware.de/addons/public-api))

## Installation

```bash
composer require four-bytes/four-lexware-api-client
```

## Quick start

```php
use Four\LexwareApiClient\LexwareClient;

$client = LexwareClient::create('your-api-key');

$profile = $client->profile()->get();
echo $profile['companyName'];

$invoice = $client->invoices()->get('e9066f04-8cc7-4616-93f8-ac9ecc8479c8');
$pdf = $client->invoices()->downloadPdf($invoice['id']);
file_put_contents('invoice.pdf', $pdf);
```

## Usage

### Creating the client

```php
use Four\LexwareApiClient\LexwareClient;

// Recommended: static factory with rate limiting and retry
$client = LexwareClient::create('your-api-key');

// Alternative: constructor with your own PSR-18 client and PSR-17 factories (e.g. for tests)
$client = new LexwareClient(
    apiKey: 'your-api-key',
    httpClient: $psrClient,
    requestFactory: $requestFactory,
    streamFactory: $streamFactory,
);
```

Every endpoint is available through a factory method (`$client->contacts()`, `$client->invoices()`, …) or can be constructed directly (`new ContactEndpoint($client)`).

### Examples

#### Create a contact (person)

```php
use Four\LexwareApiClient\DTO\Contact;
use Four\LexwareApiClient\DTO\ContactAddress;
use Four\LexwareApiClient\DTO\ContactPerson;

$contact = new Contact(
    person: new ContactPerson(
        firstName: 'Max',
        lastName: 'Mustermann',
        emailAddress: 'max@example.com',
    ),
    billingAddresses: [
        new ContactAddress(
            street: 'Musterstr. 1',
            zip: '12345',
            city: 'Berlin',
            countryCode: 'DE',
        ),
    ],
);

$contactId = $client->contacts()->create($contact);
```

#### Create a contact (company/vendor)

```php
use Four\LexwareApiClient\DTO\Contact;
use Four\LexwareApiClient\DTO\ContactAddress;
use Four\LexwareApiClient\DTO\ContactCompany;

$contact = new Contact(
    isVendor: true, // company is also a vendor
    company: new ContactCompany(
        name: 'Muster GmbH',
        vatRegistrationId: 'DE123456789',
    ),
    billingAddresses: [
        new ContactAddress(
            street: 'Businessstr. 5',
            zip: '10115',
            city: 'Berlin',
            countryCode: 'DE',
        ),
    ],
);

$contactId = $client->contacts()->create($contact);
```

#### Upsert a contact (create or update)

```php
// Creates a new contact when $existingId is null, otherwise updates it
$contactId = $client->contacts()->upsert($contact, existingId: $existingId);
```

#### Create an invoice

```php
use Four\LexwareApiClient\DTO\Invoice;
use Four\LexwareApiClient\DTO\InvoiceLineItem;

$lineItem = new InvoiceLineItem(
    name: 'Product XYZ',
    quantity: 1,
    unitName: 'piece',
    unitNetPrice: 29.90,
    taxRatePercentage: 19,
    type: InvoiceLineItem::TYPE_CUSTOM,
);

$invoice = new Invoice(
    voucherDate: '2026-02-25',
    addressName: 'Muster GmbH',
    addressStreet: 'Businessstr. 5',
    addressCity: 'Berlin',
    addressZip: '10115',
    addressCountryCode: 'DE',
    lineItems: [$lineItem],
    contactId: $contactId,
    remark: 'Order #12345',
    shippingConditionsDate: '2026-02-26',
);

$invoice = $client->invoices()->create($invoice, finalize: true);
$invoiceId = $invoice['id'];
```

#### Create a voucher (legacy way for accounting vouchers)

```php
use Four\LexwareApiClient\DTO\Voucher;
use Four\LexwareApiClient\DTO\VoucherItem;

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
    remark: 'Order #12345',
    shippingDate: '2026-02-26',
    voucherItems: [$item],
);

$voucherId = $client->vouchers()->create($voucher);
```

#### Upload a PDF to a voucher/invoice

```php
$fileId = $client->files()->uploadToVoucher($voucherId, '/path/to/invoice.pdf');
```

#### Upload an incoming invoice (accounting voucher)

```php
$fileId = $client->files()->uploadIncoming('/path/to/incoming-invoice.pdf');
```

### Download sales voucher documents

```php
// PDF bytes
$pdf = $client->invoices()->downloadPdf($invoiceId);

// File with content type and suggested file name (GET /invoices/{id}/file)
$file = $client->invoices()->downloadFile($invoiceId);
file_put_contents(basename($file->fileName ?? 'invoice.pdf'), $file->content); // never trust a server-sent path

// XRechnung as XML (ZUGFeRD and regular invoices are PDF only)
$xml = $client->invoices()->downloadFile($invoiceId, 'application/xml');
```

`downloadPdf()` and `downloadFile()` exist on invoices, credit notes, delivery notes, down payment invoices, dunnings, order confirmations and quotations. Draft vouchers have no file (HTTP 409).

### Download bookkeeping voucher files

```php
$file = $client->files()->download($fileId);           // original file
$xml  = $client->files()->download($fileId, 'application/xml'); // e-invoice XML, if present
```

`GET /files/{id}` is meant for bookkeeping vouchers only; use the sales voucher methods above for sales documents.

### Find a voucher by number

```php
$page = $client->vouchers()->findByNumber('RE-2026-0001');
```

### Register a webhook

```php
use Four\LexwareApiClient\DTO\EventSubscription;

$subscription = new EventSubscription(
    callbackUrl: 'https://my-domain.com/webhook/lexware',
    eventTypes: ['invoice.created'],
    active: true,
);

$webhook = $client->eventSubscriptions()->create($subscription);
$webhookId = $webhook['id'];

// List all webhooks
$allWebhooks = $client->eventSubscriptions()->list();

// Delete a webhook
$client->eventSubscriptions()->delete($webhookId);
```

### Deeplinks

`deeplinkUrl()` builds a link to a resource in the Lexware Office web app, so you can jump from your own application straight to the matching record:

```php
// https://app.lexware.de/permalink/contacts/view/<id>
$url = $client->deeplinkUrl('contacts', $contactId);

// Also works for invoices, vouchers, and other resources
$url = $client->deeplinkUrl('invoices', $invoiceId);
$url = $client->deeplinkUrl('vouchers', $voucherId);
```

The first argument is the resource type, the second is the resource id.

## API coverage

Every operation documented at [developers.lexware.io/docs](https://developers.lexware.io/docs/), and the client method that calls it.

| Operation | Client method |
|---|---|
| `POST /articles` | `articles()->create()` |
| `GET /articles/{id}` | `articles()->get()` |
| `PUT /articles/{id}` | `articles()->update()` |
| `DELETE /articles/{id}` | `articles()->delete()` |
| `GET /articles?…` | `articles()->list()` |
| `POST /contacts` | `contacts()->create()`, `upsert()` |
| `GET /contacts/{id}` | `contacts()->get()` |
| `PUT /contacts/{id}` | `contacts()->update()`, `upsert()` |
| `GET /contacts?…` | `contacts()->list()`, `findByEmail()`, `findByName()` |
| `GET /countries` | `countries()->list()` |
| `POST /credit-notes[?finalize]` | `creditNotes()->create()` |
| `POST /credit-notes?precedingSalesVoucherId` | `creditNotes()->pursue()` |
| `GET /credit-notes/{id}` | `creditNotes()->get()` |
| `GET /credit-notes/{id}/file` | `creditNotes()->downloadFile()`, `downloadPdf()` |
| `POST /delivery-notes[?finalize]` | `deliveryNotes()->create()` |
| `POST /delivery-notes?precedingSalesVoucherId` | `deliveryNotes()->pursue()` |
| `GET /delivery-notes/{id}` | `deliveryNotes()->get()` |
| `GET /delivery-notes/{id}/file` | `deliveryNotes()->downloadFile()`, `downloadPdf()` |
| `GET /down-payment-invoices/{id}` | `downPaymentInvoices()->get()` |
| `GET /down-payment-invoices/{id}/file` | `downPaymentInvoices()->downloadFile()`, `downloadPdf()` |
| `POST /dunnings?precedingSalesVoucherId` | `dunnings()->create()`, `pursue()` |
| `GET /dunnings/{id}` | `dunnings()->get()` |
| `GET /dunnings/{id}/file` | `dunnings()->downloadFile()`, `downloadPdf()` |
| `POST /event-subscriptions` | `eventSubscriptions()->create()` |
| `GET /event-subscriptions/{id}` | `eventSubscriptions()->get()` |
| `GET /event-subscriptions` | `eventSubscriptions()->list()` |
| `DELETE /event-subscriptions/{id}` | `eventSubscriptions()->delete()` |
| `POST /files` | `files()->uploadIncoming()` |
| `GET /files/{id}` | `files()->download()` |
| `POST /invoices[?finalize]` | `invoices()->create()` |
| `POST /invoices?precedingSalesVoucherId` | `invoices()->pursue()` |
| `GET /invoices/{id}` | `invoices()->get()` |
| `GET /invoices/{id}/file` | `invoices()->downloadFile()`, `downloadPdf()` |
| `POST /order-confirmations[?finalize]` | `orderConfirmations()->create()` |
| `POST /order-confirmations?precedingSalesVoucherId` | `orderConfirmations()->pursue()` |
| `GET /order-confirmations/{id}` | `orderConfirmations()->get()` |
| `GET /order-confirmations/{id}/file` | `orderConfirmations()->downloadFile()`, `downloadPdf()` |
| `GET /payment-conditions` | `paymentConditions()->list()` |
| `GET /payments/{voucherId}` | `payments()->get()` |
| `GET /posting-categories` | `postingCategories()->list()` |
| `GET /print-layouts` | `printLayouts()->list()` |
| `GET /profile` | `profile()->get()` |
| `POST /quotations[?finalize]` | `quotations()->create()` |
| `GET /quotations/{id}` | `quotations()->get()` |
| `GET /quotations/{id}/file` | `quotations()->downloadFile()`, `downloadPdf()` |
| `GET /recurring-templates/{id}` | `recurringTemplates()->get()` |
| `GET /recurring-templates?page&size&sort` | `recurringTemplates()->list()` |
| `GET /voucherlist?…` | `voucherlist()->list()` |
| `POST /vouchers` | `vouchers()->create()` |
| `GET /vouchers/{id}` | `vouchers()->get()` |
| `PUT /vouchers/{id}` | `vouchers()->update()` |
| `GET /vouchers?voucherNumber` | `vouchers()->findByNumber()` |
| `POST /vouchers/{id}/files` | `files()->uploadToVoucher()` |

The deprecated `GET /{type}/{id}/document` endpoints are intentionally not used.

## Posting category IDs

`VoucherItem` provides constants for common posting categories. The UUIDs are Lexware's public IDs, identical for every organization; `postingCategories()->list()` returns the full list.

| Constant | UUID | Description |
|----------|------|-------------|
| `CATEGORY_INLAND_SALES` | `8f8664a8-fd86-11e1-a21f-0800200c9a66` | Domestic sales revenue (default) |
| `CATEGORY_EU_SALES` | `4ebd965a-7126-416c-9d8c-a5c9366ee473` | EU sales revenue (private customers, OSS) |
| `CATEGORY_EU_DEALER_VAT_FREE` | `9075a4e3-66de-4795-a016-3889feca0d20` | EU dealers, VAT-free (§4 UStG) |
| `CATEGORY_WORLD_EXPORT` | `93d24c20-ea84-424e-a731-5e1b78d1e6a9` | Export deliveries to third countries |
| `CATEGORY_SERVICE_FEES` | `8f8664a7-fd86-11e1-a21f-0800200c9a66` | Service revenue |
| `CATEGORY_REVENUE` | `8f8664a1-fd86-11e1-a21f-0800200c9a66` | Other revenue |
| `CATEGORY_SERVICES` | `8f8664a0-fd86-11e1-a21f-0800200c9a66` | Services |
| `CATEGORY_DISTANCE_SALES_EU` | `7c112b66-0565-479c-bc18-5845e080880a` | Distance sales within the EU |
| `CATEGORY_DISTANCE_SALES_EU_TAXABLE` | `4ebd965a-7126-416c-9d8c-a5c9366ee473` | Alias of `CATEGORY_EU_SALES` |
| `CATEGORY_THIRD_COUNTRY_SERVICE` | `ef5b1a6e-f690-4004-9a19-91276348894f` | Services to third countries |
| `CATEGORY_SMALL_BUSINESS` | `7a1efa0e-6283-4cbf-9583-8e88d3ba5960` | Small business |

## Error handling

```php
use Four\LexwareApiClient\Exception\LexwareException;
use Four\LexwareApiClient\Exception\RateLimitException;

try {
    $client->invoices()->create($invoice);
} catch (RateLimitException $e) {
    // still HTTP 429 after 3 attempts
} catch (LexwareException $e) {
    echo $e->getHttpStatusCode() . ': ' . $e->getMessage();
    echo $e->getResponseBody(); // Lexware's error details (JSON)
}
```

`RateLimitException` extends `LexwareException`. Network errors are reported as `LexwareException` with status code `0`.

## Rate limiting

The API allows 2 requests per second. `LexwareClient::create()` throttles to 1 request per second and retries HTTP 429 up to 3 times, using the middleware of [`four-bytes/four-http-client`](https://github.com/four-bytes/four-http-client) and [`four-bytes/four-rate-limiting`](https://github.com/four-bytes/four-rate-limiting). No manual `sleep()` is needed.

## Testing

```bash
composer install
composer test      # PHPUnit
composer phpstan   # PHPStan level 6
```

The tests use a mock PSR-18 client; no API key or network access is required.

## Related packages

- [`four-bytes/four-http-client`](https://github.com/four-bytes/four-http-client) — PSR-18 client factory with retry and rate-limiting middleware
- [`four-bytes/four-rate-limiting`](https://github.com/four-bytes/four-rate-limiting) — the rate limiter behind the middleware

## Contributing, security, changelog

- Contributing: [CONTRIBUTING.md](CONTRIBUTING.md)
- Security: [SECURITY.md](SECURITY.md) — please report vulnerabilities privately via GitHub's private vulnerability reporting
- Changelog: [HISTORY.md](HISTORY.md)

## License

Apache-2.0 — see [LICENSE](LICENSE) and [NOTICE](NOTICE).
