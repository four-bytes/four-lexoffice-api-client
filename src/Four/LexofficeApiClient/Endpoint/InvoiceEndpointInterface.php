<?php

declare(strict_types=1);

namespace Four\LexofficeApiClient\Endpoint;

use Four\LexofficeApiClient\DTO\Invoice;
use Four\LexofficeApiClient\Exception\LexofficeException;

interface InvoiceEndpointInterface
{
    /**
     * Erstellt eine neue Rechnung (draft oder direkt finalisiert).
     *
     * @return array<string, mixed>  {id, resourceUri, version}
     * @throws LexofficeException
     */
    public function create(Invoice $invoice, bool $finalize = false): array;

    /**
     * Leitet einen bestehenden Voucher in eine neue Rechnung über (Pursue).
     *
     * @return array<string, mixed>  {id, resourceUri, version}
     * @throws LexofficeException
     */
    public function pursue(string $precedingId, Invoice $invoice, bool $finalize = false): array;

    /**
     * Gibt eine Rechnung anhand der ID zurück.
     *
     * @return array<string, mixed>
     * @throws LexofficeException
     */
    public function get(string $id): array;

    /**
     * Lädt die PDF-Datei einer Rechnung herunter und gibt die rohen Bytes zurück.
     *
     * @throws LexofficeException
     */
    public function downloadPdf(string $id): string;
}
