<?php

declare(strict_types=1);

namespace Four\LexofficeApiClient\Endpoint;

use Four\LexofficeApiClient\DTO\Quotation;
use Four\LexofficeApiClient\Exception\LexofficeException;

interface QuotationEndpointInterface
{
    /**
     * Creates a new quotation.
     *
     * @return array<string, mixed>  {id, resourceUri, version}
     * @throws LexofficeException
     */
    public function create(Quotation $quotation, bool $finalize = false): array;

    /**
     * Converts an existing voucher into a new quotation (Pursue).
     *
     * @return array<string, mixed>  {id, resourceUri, version}
     * @throws LexofficeException
     */
    public function pursue(string $precedingId, Quotation $quotation, bool $finalize = false): array;

    /**
     * Returns a quotation by ID.
     *
     * @return array<string, mixed>
     * @throws LexofficeException
     */
    public function get(string $id): array;

    /**
     * Downloads the PDF file of a quotation.
     *
     * @throws LexofficeException
     */
    public function downloadPdf(string $id): string;
}
