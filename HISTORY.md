# History

## 0.1.0 — 2026-02-25
- Initiales Release
- LexofficeClient: HTTP-Client mit Rate Limiting (1 req/s) + Retry (3x bei 429)
- DTOs: Contact, Voucher, VoucherItem, FileUpload
- Endpoints: ContactEndpoint, VoucherEndpoint, FileEndpoint
- Exceptions: LexofficeException, RateLimitException
- Portiert aus dev-playground/src/Four/Financial/LexwareClient.php

## 0.2.0 — 2026-02-25
- LexofficeClient: BASE_URL auf api.lexware.io korrigiert (war lexoffice.io)
- LexofficeClient: `downloadFile()` für PDF-Download (Accept: application/pdf, raw bytes)
- DTO: PagedResponse (generisches Paging mit fromArray)
- DTO: Article + ArticlePrice (PRODUCT|SERVICE, toApiArray)
- DTO: InvoiceLineItem (custom|material|service|text, unitPrice-Struktur)
- DTO: Invoice, CreditNote, Quotation, OrderConfirmation (taxConditions, paymentConditions)
- DTO: DeliveryNote (wie Invoice, ohne taxConditions/paymentConditions)
- DTO: Payment (read-only, fromArray)
- DTO: EventSubscription (20 EVENT_*-Konstanten, toApiArray)
- VoucherItem: 5 neue Kategorie-Konstanten (SERVICES, DISTANCE_SALES_EU, DISTANCE_SALES_EU_TAXABLE, THIRD_COUNTRY_SERVICE, SMALL_BUSINESS)
- Endpoint: ArticleEndpoint (create, get, update, delete, list mit Paging)
- Endpoint: InvoiceEndpoint (create, pursue, get, downloadPdf)
- Endpoint: CreditNoteEndpoint (create, pursue, get, downloadPdf)
- Endpoint: DeliveryNoteEndpoint (create, get, downloadPdf)
- Endpoint: QuotationEndpoint (create, pursue, get, downloadPdf)
- Endpoint: OrderConfirmationEndpoint (create, pursue, get, downloadPdf)
- Endpoint: PaymentEndpoint (get → Payment DTO)
- Endpoint: EventSubscriptionEndpoint (create, get, list, delete)
- Endpoint: VoucherlistEndpoint (list mit Filtern + PagedResponse)
- Endpoint: CountryEndpoint, ProfileEndpoint, PaymentConditionEndpoint, PostingCategoryEndpoint, PrintLayoutEndpoint, RecurringTemplateEndpoint (alle read-only)
- ContactEndpoint: findByEmail(), findByName(), list() mit PagedResponse ergänzt
- Tests: 42 Tests, 166 Assertions (alle grün)

## 0.3.0 — 2026-02-26
- Replaced raw cURL transport with PSR-18 HTTP client interface
- Added `LexofficeClient::create(string $apiKey)` static factory using four-http-client
- Rate limiting (1 req/s) now handled by four-rate-limiting middleware
- Retry logic (3x on 429) now handled by four-http-client RetryMiddleware
- Removed `ext-curl` dependency
- Added `four-bytes/four-http-client ^4.0` and `four-bytes/four-rate-limiting ^1.3`
- Added PHPUnit 11 tests for LexofficeClient transport layer
