# History

## 0.1.0 — 2026-02-25
- Initiales Release
- LexofficeClient: HTTP-Client mit Rate Limiting (1 req/s) + Retry (3x bei 429)
- DTOs: Contact, Voucher, VoucherItem, FileUpload
- Endpoints: ContactEndpoint, VoucherEndpoint, FileEndpoint
- Exceptions: LexofficeException, RateLimitException
- Portiert aus dev-playground/src/Four/Financial/LexwareClient.php
