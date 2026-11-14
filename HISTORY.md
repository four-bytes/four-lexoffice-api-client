# History

## 1.0.2 — 2026-11-14
- docs: `LICENSE` is now the unmodified Apache-2.0 text; copyright notice moved to new `NOTICE` (rights holder Four Flames GmbH & Co. KG)

## 1.0.1 — 2026-11-14
- Renamed the package and namespace from "lexoffice" to the current Lexware brand: `four-bytes/four-lexware-api-client`, `Four\LexwareApiClient\`, `LexwareClient`, `LexwareException` (the API was rebranded to Lexware on 2025-05-26)

## 0.5.1 — 2026-02-27
- fix: `taxRatePercent` as float (was int) — now supports fractional tax rates such as 25.5% (Finland, Denmark)

## 0.5.0 — 2026-02-26
- **Breaking Change:** Contact DTO migrated to the nested API structure
- New DTOs: ContactCompany, ContactPerson
- ContactAddress: added `fromArray()`
- Contact: `isCustomer`/`isVendor` instead of `isCompany`/`isVendor`, `company`/`person` objects, `billingAddresses[]`, `emailsBusiness/Office/Private/Other[]`, `phonesBusiness/Office/Mobile/Private/Fax/Other[]`, `xRechnungBuyerReference`, `xRechnungVendorNumber`, `fromArray()`
- LexwareClient: 20 endpoint factory methods (`contacts()`, `invoices()`, `articles()`, etc.)
- Tests: 61 tests, 207 assertions (all green)

## 0.4.0 — 2026-02-26
- Endpoint: DunningEndpoint (dunning) — create, pursue, get, downloadPdf
- Endpoint: DownPaymentInvoiceEndpoint (down payment invoices) — get, downloadPdf (read-only)
- VoucherEndpoint: added `update()`
- DTO: `fromArray()` for all DTOs — Invoice, CreditNote, Quotation, DeliveryNote, Dunning, Article, Voucher, VoucherItem, EventSubscription, InvoiceLineItem
- Tests: 59 tests, 191 assertions (all green)

## 0.3.0 — 2026-02-26
- Replaced raw cURL transport with the PSR-18 HTTP client interface
- Added `LexwareClient::create(string $apiKey)` static factory using four-http-client
- Rate limiting (1 req/s) is now handled by four-rate-limiting middleware
- Retry logic (3x on 429) is now handled by four-http-client RetryMiddleware
- Removed the `ext-curl` dependency
- Added `four-bytes/four-http-client ^4.0` and `four-bytes/four-rate-limiting ^1.3`
- Added PHPUnit 11 tests for the LexwareClient transport layer

## 0.2.0 — 2026-02-25
- LexwareClient: fixed BASE_URL to the Lexware API host `api.lexware.io`
- LexwareClient: `downloadFile()` for PDF downloads (Accept: application/pdf, raw bytes)
- DTO: PagedResponse (generic paging with fromArray)
- DTO: Article + ArticlePrice (PRODUCT|SERVICE, toApiArray)
- DTO: InvoiceLineItem (custom|material|service|text, unitPrice structure)
- DTO: Invoice, CreditNote, Quotation, OrderConfirmation (taxConditions, paymentConditions)
- DTO: DeliveryNote (like Invoice, without taxConditions/paymentConditions)
- DTO: Payment (read-only, fromArray)
- DTO: EventSubscription (20 EVENT_* constants, toApiArray)
- VoucherItem: 5 new category constants (SERVICES, DISTANCE_SALES_EU, DISTANCE_SALES_EU_TAXABLE, THIRD_COUNTRY_SERVICE, SMALL_BUSINESS)
- Endpoint: ArticleEndpoint (create, get, update, delete, list with paging)
- Endpoint: InvoiceEndpoint (create, pursue, get, downloadPdf)
- Endpoint: CreditNoteEndpoint (create, pursue, get, downloadPdf)
- Endpoint: DeliveryNoteEndpoint (create, get, downloadPdf)
- Endpoint: QuotationEndpoint (create, pursue, get, downloadPdf)
- Endpoint: OrderConfirmationEndpoint (create, pursue, get, downloadPdf)
- Endpoint: PaymentEndpoint (get → Payment DTO)
- Endpoint: EventSubscriptionEndpoint (create, get, list, delete)
- Endpoint: VoucherlistEndpoint (list with filters + PagedResponse)
- Endpoint: CountryEndpoint, ProfileEndpoint, PaymentConditionEndpoint, PostingCategoryEndpoint, PrintLayoutEndpoint, RecurringTemplateEndpoint (all read-only)
- ContactEndpoint: added findByEmail(), findByName(), list() with PagedResponse
- Tests: 42 tests, 166 assertions (all green)

## 0.1.0 — 2026-02-25
- Initial release
- LexwareClient: HTTP client with rate limiting (1 req/s) + retry (3x on 429)
- DTOs: Contact, Voucher, VoucherItem, FileUpload
- Endpoints: ContactEndpoint, VoucherEndpoint, FileEndpoint
- Exceptions: LexwareException, RateLimitException
- Ported from an internal legacy codebase
