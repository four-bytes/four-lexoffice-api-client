<?php

declare(strict_types=1);

namespace Four\LexofficeApiClient\Endpoint;

use Four\LexofficeApiClient\DTO\DeliveryNote;
use Four\LexofficeApiClient\Exception\LexofficeException;

interface DeliveryNoteEndpointInterface
{
    /**
     * Erstellt einen neuen Lieferschein.
     *
     * @return array<string, mixed>  {id, resourceUri, version}
     * @throws LexofficeException
     */
    public function create(DeliveryNote $deliveryNote, bool $finalize = false): array;

    /**
     * Gibt einen Lieferschein anhand der ID zurück.
     *
     * @return array<string, mixed>
     * @throws LexofficeException
     */
    public function get(string $id): array;

    /**
     * Lädt die PDF-Datei eines Lieferscheins herunter.
     *
     * @throws LexofficeException
     */
    public function downloadPdf(string $id): string;
}
