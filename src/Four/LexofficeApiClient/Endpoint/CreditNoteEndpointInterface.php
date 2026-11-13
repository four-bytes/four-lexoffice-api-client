<?php

declare(strict_types=1);

namespace Four\LexofficeApiClient\Endpoint;

use Four\LexofficeApiClient\DTO\CreditNote;
use Four\LexofficeApiClient\Exception\LexofficeException;

interface CreditNoteEndpointInterface
{
    /**
     * Creates a new credit note.
     *
     * @return array<string, mixed>  {id, resourceUri, version}
     * @throws LexofficeException
     */
    public function create(CreditNote $creditNote, bool $finalize = false): array;

    /**
     * Converts an existing voucher into a new credit note (Pursue).
     *
     * @return array<string, mixed>  {id, resourceUri, version}
     * @throws LexofficeException
     */
    public function pursue(string $precedingId, CreditNote $creditNote, bool $finalize = false): array;

    /**
     * Returns a credit note by ID.
     *
     * @return array<string, mixed>
     * @throws LexofficeException
     */
    public function get(string $id): array;

    /**
     * Downloads the PDF file of a credit note.
     *
     * @throws LexofficeException
     */
    public function downloadPdf(string $id): string;
}
