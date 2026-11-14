<?php

declare(strict_types=1);

namespace Four\LexwareApiClient\Endpoint;

use Four\LexwareApiClient\DTO\DeliveryNote;
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
     * Downloads the PDF file of a delivery note.
     *
     * @throws LexwareException
     */
    public function downloadPdf(string $id): string;
}
