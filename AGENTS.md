# four-lexware-api-client

PHP API client for the Lexware Office API v1. The API was formerly branded
"lexoffice" and was rebranded to Lexware on 2025-05-26.

## Language
English — code, docs, issues, PRs and commits are written in English.

## Project
- Company: Four Bytes
- Org: four-bytes
- Purpose: create and manage invoices, contacts and vouchers in Lexware Office

## Stack
- PHP 8.4+
- PSR-4 autoloading
- Dependencies: four-bytes/four-http-client (PSR-18), four-bytes/four-rate-limiting

## API
- Base URL: https://api.lexware.io/v1/
- Auth: Bearer Token
- Rate Limit: 2 req/s (client throttles to 1 req/s for headroom)
- Docs: https://developers.lexware.io/docs/ (source of truth for implementation, tests and mocks)

## Key paths
- Client: src/Four/LexwareApiClient/LexwareClient.php
- DTOs: src/Four/LexwareApiClient/DTO/
- Endpoints: src/Four/LexwareApiClient/Endpoint/
