<?php

declare(strict_types=1);

namespace Four\LexwareApiClient\Endpoint;

use Four\LexwareApiClient\DTO\DownloadedFile;
use Four\LexwareApiClient\DTO\Quotation;
use Four\LexwareApiClient\Exception\LexwareException;

interface QuotationEndpointInterface
{
    /**
     * Creates a new quotation.
     *
     * @return array<string, mixed>  {id, resourceUri, version}
     * @throws LexwareException
     */
    public function create(Quotation $quotation, bool $finalize = false): array;

    /**
     * Converts an existing voucher into a new quotation (Pursue).
     *
     * @return array<string, mixed>  {id, resourceUri, version}
     * @throws LexwareException
     * @deprecated The API documents no pursue action to quotations; will be removed in 2.0.
     */
    public function pursue(string $precedingId, Quotation $quotation, bool $finalize = false): array;

    /**
     * Returns a quotation by ID.
     *
     * @return array<string, mixed>
     * @throws LexwareException
     */
    public function get(string $id): array;

    /**
     * Downloads the PDF file of a quotation and returns the raw bytes.
     *
     * @throws LexwareException
     */
    public function downloadPdf(string $id): string;

    /**
     * Downloads the document file of a quotation (GET /quotations/{id}/file).
     *
     * @param string $accept Accept header: the wildcard default, application/pdf or application/xml (XRechnung only)
     * @throws LexwareException
     */
    public function downloadFile(string $id, string $accept = '*/*'): DownloadedFile;
}
