<?php

declare(strict_types=1);

namespace Four\LexwareApiClient\Endpoint;

use Four\LexwareApiClient\DTO\DownloadedFile;
use Four\LexwareApiClient\DTO\Dunning;
use Four\LexwareApiClient\Exception\LexwareException;

interface DunningEndpointInterface
{
    /**
     * Creates a dunning for an invoice or down payment invoice (POST /dunnings?precedingSalesVoucherId={id}).
     *
     * Dunnings are always created as drafts.
     *
     * @param string $precedingSalesVoucherId ID of the invoice to be dunned (mandatory per API docs)
     * @return array<string, mixed>  {id, resourceUri, version}
     * @throws LexwareException
     */
    public function create(Dunning $dunning, string $precedingSalesVoucherId): array;

    /**
     * Pursues an invoice to a new dunning — same API call as create().
     *
     * @param bool $finalize Deprecated: not documented for dunnings (always created as drafts); will be removed in 2.0.
     * @return array<string, mixed>  {id, resourceUri, version}
     * @throws LexwareException
     */
    public function pursue(string $precedingId, Dunning $dunning, bool $finalize = false): array;

    /**
     * Returns a dunning notice by ID.
     *
     * @return array<string, mixed>
     * @throws LexwareException
     */
    public function get(string $id): array;

    /**
     * Downloads the PDF file of a dunning and returns the raw bytes.
     *
     * @throws LexwareException
     */
    public function downloadPdf(string $id): string;

    /**
     * Downloads the document file of a dunning (GET /dunnings/{id}/file).
     *
     * @param string $accept Accept header: the wildcard default, application/pdf or application/xml (XRechnung only)
     * @throws LexwareException
     */
    public function downloadFile(string $id, string $accept = '*/*'): DownloadedFile;
}
