# four-lexoffice-api-client

PHP API Client für die lexoffice / Lexware Office API v1.

## Projekt
- Firma: fourbytes
- Org: four-bytes
- Zweck: Rechnungen und Kontakte in lexoffice anlegen/verwalten
- Genutzt von: four-commerce-etsy, webinvo

## Stack
- PHP 8.2+
- Kein Framework, keine externe Dependency
- PSR-4 Autoloading

## API
- Base URL: https://api.lexoffice.io/v1/
- Auth: Bearer Token
- Rate Limit: 2 req/s (wir limitieren auf 1 req/s)
- Docs: https://developers.lexoffice.io/docs/

## Wichtige Pfade
- Client: src/Four/LexofficeApiClient/LexofficeClient.php
- DTOs: src/Four/LexofficeApiClient/DTO/
- Endpoints: src/Four/LexofficeApiClient/Endpoint/
