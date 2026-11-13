# four-lexoffice-api-client

PHP client for the [lexoffice / Lexware Office API v1](https://developers.lexoffice.io/docs/).

Full support for all 20 endpoints: Articles, Contacts, Invoices, CreditNotes, DeliveryNotes, Quotations, OrderConfirmations, Dunning, DownPaymentInvoices, Payments, EventSubscriptions, Vouchers, Files, Voucherlist plus the lookup endpoints.

Built on `four-bytes/four-http-client` (PSR-18). Rate limiting and retries are handled by middleware — no direct cURL dependency.

> **Disclaimer:** This is an unofficial, community-maintained client. It is not affiliated with, endorsed by, or sponsored by Lexware GmbH & Co. KG or lexoffice.

**API base URL:** `https://api.lexware.io/v1/`

**App URL:** `https://app.lexware.de`

**Rate limit:** 2 requests/second on the API side (the client throttles to 1 req/s for headroom)

---

## Installation

```bash
composer require four-bytes/four-lexoffice-api-client
```

---

## All available endpoints

| Endpoint | Class | Description |
|----------|-------|-------------|
| Articles | `ArticleEndpoint` | Article management |
| Contacts | `ContactEndpoint` | Contacts (people and companies) |
| Invoices | `InvoiceEndpoint` | Outgoing invoices |
| CreditNotes | `CreditNoteEndpoint` | Credit notes |
| DeliveryNotes | `DeliveryNoteEndpoint` | Delivery notes |
| Quotations | `QuotationEndpoint` | Quotations |
| OrderConfirmations | `OrderConfirmationEndpoint` | Order confirmations |
| Dunning | `DunningEndpoint` | Dunning notices |
| DownPaymentInvoices | `DownPaymentInvoiceEndpoint` | Down payment invoices (read-only) |
| Payments | `PaymentEndpoint` | Payments |
| Vouchers | `VoucherEndpoint` | Accounting vouchers (legacy) |
| Voucherlist | `VoucherlistEndpoint` | Voucher list |
| EventSubscriptions | `EventSubscriptionEndpoint` | Webhooks |
| Files | `FileEndpoint` | File upload/download |
| **Lookup endpoints** | | |
| Countries | `CountryEndpoint` | Country list |
| Profile | `ProfileEndpoint` | Company profile |
| PaymentConditions | `PaymentConditionEndpoint` | Payment conditions |
| PostingCategories | `PostingCategoryEndpoint` | Posting categories |
| PrintLayouts | `PrintLayoutEndpoint` | Print layouts |
| RecurringTemplates | `RecurringTemplateEndpoint` | Recurring templates |

---

## Initializing the client

```php
use Four\LexofficeApiClient\LexofficeClient;

// Recommended: static factory (a PSR-18 client is wired up automatically)
$client = LexofficeClient::create('your-api-key');

// Alternative: constructor with DI (e.g. for tests)
$client = new LexofficeClient(
    apiKey: 'your-api-key',
    httpClient: $psrClient,
    requestFactory: $requestFactory,
    streamFactory: $streamFactory,
);
```

### Registering endpoints

```php
use Four\LexofficeApiClient\Endpoint\ContactEndpoint;
use Four\LexofficeApiClient\Endpoint\InvoiceEndpoint;
use Four\LexofficeApiClient\Endpoint\VoucherEndpoint;
use Four\LexofficeApiClient\Endpoint\FileEndpoint;
use Four\LexofficeApiClient\Endpoint\EventSubscriptionEndpoint;

// All endpoints
$contacts    = new ContactEndpoint($client);
$invoices    = new InvoiceEndpoint($client);
$vouchers    = new VoucherEndpoint($client);
$files       = new FileEndpoint($client);
$webhooks    = new EventSubscriptionEndpoint($client);
```

Alternatively, the client exposes a factory method for every endpoint:

```php
$contacts = $client->contacts();
$invoices = $client->invoices();
$vouchers = $client->vouchers();
```

---

## Code examples

### Create a contact (person)

```php
use Four\LexofficeApiClient\DTO\Contact;
use Four\LexofficeApiClient\DTO\ContactAddress;
use Four\LexofficeApiClient\DTO\ContactPerson;

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

$contactId = $contacts->create($contact);
```

### Create a contact (company/vendor)

```php
use Four\LexofficeApiClient\DTO\Contact;
use Four\LexofficeApiClient\DTO\ContactAddress;
use Four\LexofficeApiClient\DTO\ContactCompany;

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

$contactId = $contacts->create($contact);
```

### Upsert a contact (create or update)

```php
// Creates a new contact when $existingId is null, otherwise updates it
$contactId = $contacts->upsert($contact, existingId: $existingId);
```

### Create an invoice

```php
use Four\LexofficeApiClient\DTO\Invoice;
use Four\LexofficeApiClient\DTO\InvoiceLineItem;

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

$invoice = $invoices->create($invoice, finalize: true);
$invoiceId = $invoice['id'];
```

### Create a voucher (legacy way for accounting vouchers)

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
    remark: 'Order #12345',
    shippingDate: '2026-02-26',
    voucherItems: [$item],
);

$voucherId = $vouchers->create($voucher);
```

### Upload a PDF to a voucher/invoice

```php
$fileId = $files->uploadToVoucher($voucherId, '/path/to/invoice.pdf');
```

### Upload an incoming invoice (accounting voucher)

```php
$fileId = $files->uploadIncoming('/path/to/incoming-invoice.pdf');
```

### Download the PDF of an invoice

```php
$pdfContent = $invoices->downloadPdf($invoiceId);

// Or for a voucher
$pdfContent = $client->downloadFile('vouchers/' . $voucherId . '/document');

// Save it
file_put_contents('/path/to/output.pdf', $pdfContent);
```

### Register a webhook

```php
use Four\LexofficeApiClient\DTO\EventSubscription;

$subscription = new EventSubscription(
    callbackUrl: 'https://my-domain.com/webhook/lexoffice',
    eventTypes: ['invoice.created'],
    active: true,
);

$webhook = $webhooks->create($subscription);
$webhookId = $webhook['id'];

// List all webhooks
$allWebhooks = $webhooks->list();

// Delete a webhook
$webhooks->delete($webhookId);
```

---

## Deeplinks

`deeplinkUrl()` builds a link to a resource in the lexoffice web app, so you can jump from your own application straight to the matching record:

```php
// https://app.lexware.de/permalink/contacts/view/<id>
$url = $client->deeplinkUrl('contacts', $contactId);

// Also works for invoices, vouchers, and other resources
$url = $client->deeplinkUrl('invoices', $invoiceId);
$url = $client->deeplinkUrl('vouchers', $voucherId);
```

The first argument is the resource type, the second is the resource id.

---

## Voucher category IDs (posting categories)

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
| `CATEGORY_DISTANCE_SALES_EU_TAXABLE` | `4ebd965a-7126-416c-9d8c-a5c9366ee473` | Distance sales within the EU (taxable) |
| `CATEGORY_THIRD_COUNTRY_SERVICE` | `ef5b1a6e-f690-4004-9a19-91276348894f` | Services to third countries |
| `CATEGORY_SMALL_BUSINESS` | `7a1efa0e-6283-4cbf-9583-8e88d3ba5960` | Small business |

---

## Error handling

```php
use Four\LexofficeApiClient\Exception\LexofficeException;
use Four\LexofficeApiClient\Exception\RateLimitException;

try {
    $invoices->create($invoice);
} catch (RateLimitException $e) {
    // Still HTTP 429 after 3 retries
    echo 'Rate limit exceeded: ' . $e->getMessage();
} catch (LexofficeException $e) {
    echo 'API error ' . $e->getHttpStatusCode() . ': ' . $e->getMessage();
    echo 'Response: ' . $e->getResponseBody();
}
```

---

## Rate limiting

Rate limiting (1 req/s) and retry logic (up to 3 attempts on HTTP 429) are handled automatically by the middleware stack of `four-bytes/four-http-client`. No manual `sleep()` or throttling is required.

---

## License

Apache-2.0 — Four Bytes
