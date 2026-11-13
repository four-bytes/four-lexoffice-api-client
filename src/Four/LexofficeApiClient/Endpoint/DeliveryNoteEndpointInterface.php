<?php

declare(strict_types=1);

namespace Four\LexofficeApiClient\Endpoint;

use Four\LexofficeApiClient\DTO\DeliveryNote;
use Four\LexofficeApiClient\Exception\LexofficeException;

interface DeliveryNoteEndpointInterface
{
    /**
     * Creates a new delivery note.
     *
     * @return array<string, mixed>  {id, resourceUri, version}
     * @throws LexofficeException
     */
    public function create(DeliveryNote $deliveryNote, bool $finalize = false): array;

    /**
     * Returns a delivery note by ID.
     *
     * @return array<string, mixed>
     * @throws LexofficeException
     */
    public function get(string $id): array;

    /**
     * Downloads the PDF file of a delivery note.
     *
     * @throws LexofficeException
     */
    public function downloadPdf(string $id): string;
}
