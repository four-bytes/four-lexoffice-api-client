<?php

declare(strict_types=1);

namespace Four\LexwareApiClient\Endpoint;

use Four\LexwareApiClient\DTO\DeliveryNote;
use Four\LexwareApiClient\DTO\DownloadedFile;
use Four\LexwareApiClient\Exception\LexwareException;

interface DeliveryNoteEndpointInterface
{
    /**
     * Creates a new delivery note.
     *
     * @return array<string, mixed>  {id, resourceUri, version}
     * @throws LexwareException
     */
    public function create(DeliveryNote $deliveryNote, bool $finalize = false): array;

    /**
     * Returns a delivery note by ID.
     *
     * @return array<string, mixed>
     * @throws LexwareException
     */
    public function get(string $id): array;

    /**
     * Downloads the PDF file of a delivery note and returns the raw bytes.
     *
     * @throws LexwareException
     */
    public function downloadPdf(string $id): string;

    /**
     * Downloads the document file of a delivery note (GET /delivery-notes/{id}/file).
     *
     * @param string $accept Accept header: the wildcard default, application/pdf or application/xml (XRechnung only)
     * @throws LexwareException
     */
    public function downloadFile(string $id, string $accept = '*/*'): DownloadedFile;

    /**
     * Pursues an existing sales voucher to a new delivery note.
     *
     * @return array<string, mixed>  {id, resourceUri, version}
     * @throws LexwareException
     */
    public function pursue(string $precedingId, DeliveryNote $deliveryNote): array;
}
