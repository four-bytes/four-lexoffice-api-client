# four-lexoffice-api-client

PHP API client for the lexoffice / Lexware Office API v1.

## Language
English — code, docs, issues, PRs and commits are written in English.

## Project
- Company: Four Bytes
- Org: four-bytes
- Purpose: create and manage invoices, contacts and vouchers in lexoffice

## Stack
- PHP 8.4+
- PSR-4 autoloading
- Dependencies: four-bytes/four-http-client (PSR-18), four-bytes/four-rate-limiting

## API
- Base URL: https://api.lexoffice.io/v1/
- Auth: Bearer Token
- Rate Limit: 2 req/s (client throttles to 1 req/s for headroom)
- Docs: https://developers.lexoffice.io/docs/

## Key paths
- Client: src/Four/LexofficeApiClient/LexofficeClient.php
- DTOs: src/Four/LexofficeApiClient/DTO/
- Endpoints: src/Four/LexofficeApiClient/Endpoint/
