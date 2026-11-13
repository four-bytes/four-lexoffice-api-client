<?php

declare(strict_types=1);

namespace Four\LexofficeApiClient\Endpoint;

use Four\LexofficeApiClient\DTO\Invoice;
use Four\LexofficeApiClient\Exception\LexofficeException;

interface InvoiceEndpointInterface
{
    /**
     * Creates a new invoice (draft or directly finalized).
     *
     * @return array<string, mixed>  {id, resourceUri, version}
     * @throws LexofficeException
     */
    public function create(Invoice $invoice, bool $finalize = false): array;

    /**
     * Converts an existing voucher into a new invoice (Pursue).
     *
     * @return array<string, mixed>  {id, resourceUri, version}
     * @throws LexofficeException
     */
    public function pursue(string $precedingId, Invoice $invoice, bool $finalize = false): array;

    /**
     * Returns an invoice by ID.
     *
     * @return array<string, mixed>
     * @throws LexofficeException
     */
    public function get(string $id): array;

    /**
     * Downloads the PDF file of an invoice and returns the raw bytes.
     *
     * @throws LexofficeException
     */
    public function downloadPdf(string $id): string;
}
