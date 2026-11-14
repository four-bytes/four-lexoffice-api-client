<?php

declare(strict_types=1);

namespace Four\LexwareApiClient\Endpoint;

use Four\LexwareApiClient\DTO\DownloadedFile;
use Four\LexwareApiClient\DTO\Dunning;
use Four\LexwareApiClient\Exception\LexwareException;

interface DunningEndpointInterface
{
    /**
     * Creates a new dunning notice (draft or directly finalized).
     *
     * @return array<string, mixed>  {id, resourceUri, version}
     * @throws LexwareException
     */
    public function create(Dunning $dunning, bool $finalize = false): array;

    /**
     * Converts an existing voucher into a new dunning notice (Pursue).
     *
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
