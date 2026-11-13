<?php

declare(strict_types=1);

namespace Four\LexofficeApiClient\Endpoint;

use Four\LexofficeApiClient\DTO\Dunning;
use Four\LexofficeApiClient\Exception\LexofficeException;

interface DunningEndpointInterface
{
    /**
     * Creates a new dunning notice (draft or directly finalized).
     *
     * @return array<string, mixed>  {id, resourceUri, version}
     * @throws LexofficeException
     */
    public function create(Dunning $dunning, bool $finalize = false): array;

    /**
     * Converts an existing voucher into a new dunning notice (Pursue).
     *
     * @return array<string, mixed>  {id, resourceUri, version}
     * @throws LexofficeException
     */
    public function pursue(string $precedingId, Dunning $dunning, bool $finalize = false): array;

    /**
     * Returns a dunning notice by ID.
     *
     * @return array<string, mixed>
     * @throws LexofficeException
     */
    public function get(string $id): array;

    /**
     * Downloads the PDF file of a dunning notice and returns the raw bytes.
     *
     * @throws LexofficeException
     */
    public function downloadPdf(string $id): string;
}
