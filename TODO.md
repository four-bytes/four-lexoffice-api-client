# TODO — four-lexoffice-api-client

## Fehlende Endpoints (API vorhanden, nicht implementiert)

### DunningEndpoint (Mahnwesen)
- [ ] `src/Four/LexofficeApiClient/Endpoint/DunningEndpoint.php`
  - `create(string $precedingSalesVoucherId): array` — pursue from invoice
  - `pursue(string $precedingId): array`
  - `get(string $id): array`
  - `downloadPdf(string $id): string`
- [ ] `src/Four/LexofficeApiClient/DTO/Dunning.php` — identisch zu Invoice-Struktur
- [ ] Tests: `tests/Endpoint/DunningEndpointTest.php`

### DownPaymentInvoicesEndpoint (Abschlagsrechnungen)
- [ ] `src/Four/LexofficeApiClient/Endpoint/DownPaymentInvoiceEndpoint.php`
  - `get(string $id): array` — read-only
  - `downloadPdf(string $id): string`
- [ ] Tests: `tests/Endpoint/DownPaymentInvoiceEndpointTest.php`

## Contact DTO — Strukturelle Verbesserung (Breaking Change)

Das aktuelle `Contact`-DTO ist flach (einzelne Properties) — die API erwartet/liefert eine verschachtelte Struktur:

```php
// Aktuell (flach, unvollständig):
$contact->firstName, $contact->companyName, $contact->email

// API-Struktur (verschachtelt):
roles: { customer: {number}, vendor: {number} }
company: { name, taxNumber, vatRegistrationId, contactPersons[] }
person: { salutation, firstName, lastName }
addresses: { billing: [], shipping: [] }
emailAddresses: { business: [], office: [], private: [], other: [] }
phoneNumbers: { business: [], mobile: [], fax: [], ... }
xRechnung: { buyerReference, vendorNumberAtCustomer }
note, archived
```

- [ ] `Contact.php` auf verschachtelte Struktur umstellen
- [ ] Neue DTOs: `ContactPerson.php`, `ContactRole.php`, `ContactAddresses.php`
- [ ] `fromArray()` für alle Contact-DTOs (für API-Response-Parsing)
- [ ] Migration: `ContactEndpoint` anpassen, Tests aktualisieren
- [ ] **Achtung:** Breaking Change — Konsumenten (four-commerce-etsy, webinvo) müssen angepasst werden

## Verbesserungen (kein Breaking Change)

### fromArray() für alle DTOs
Fast alle DTOs haben nur `toApiArray()` aber kein `fromArray()` — API-Responses können damit nicht geparst werden.
- [ ] `Invoice::fromArray()`
- [ ] `CreditNote::fromArray()`
- [ ] `Quotation::fromArray()`
- [ ] `OrderConfirmation::fromArray()`
- [ ] `DeliveryNote::fromArray()`
- [ ] `Article::fromArray()`
- [ ] `EventSubscription::fromArray()`
- [ ] `Voucher::fromArray()`

### Deeplinks
Die API bietet für viele Ressourcen Deeplinks zur Lexware-App — praktisch für UI-Integration.
- [ ] `LexofficeClient::deeplinkUrl(string $resource, string $id): string`
  - Contacts: `{app}/permalink/contacts/view/{id}`
  - Invoices: `{app}/permalink/invoices/view/{id}`
  - etc.

### VoucherEndpoint: update()
- [ ] `VoucherEndpoint::update(string $id, Voucher $voucher): array` — laut API vorhanden

### Endpoint-Factory im Client
- [ ] `LexofficeClient::contacts(): ContactEndpoint`
- [ ] `LexofficeClient::invoices(): InvoiceEndpoint`
- [ ] etc. — statt manuell `new ContactEndpoint($client)`

## Qualität

- [ ] PHPStan Level auf 8 erhöhen (aktuell: 6)
- [ ] Interfaces für alle Endpoints (`ContactEndpointInterface` etc.)
- [ ] GitHub Actions CI (PHPUnit + PHPStan)
