<?php

declare(strict_types=1);

namespace Four\LexofficeApiClient\Endpoint;

use Four\LexofficeApiClient\DTO\DeliveryNote;
use Four\LexofficeApiClient\Exception\LexofficeException;
use Four\LexofficeApiClient\LexofficeClient;

class DeliveryNoteEndpoint implements DeliveryNoteEndpointInterface
{
    public function __construct(
        private readonly LexofficeClient $client,
    ) {
    }

    /**
     * Erstellt einen neuen Lieferschein.
     *
     * @return array<string, mixed>  {id, resourceUri, version}
     * @throws LexofficeException
     */
    public function create(DeliveryNote $deliveryNote, bool $finalize = false): array
    {
        $endpoint = $finalize ? 'delivery-notes?finalize=true' : 'delivery-notes';

        /** @var array<string, mixed> $result */
        $result = $this->client->sendRequest($endpoint, $deliveryNote->toApiArray(), 'POST');
        return $result;
    }

    /**
     * Gibt einen Lieferschein anhand der ID zurück.
     *
     * @return array<string, mixed>
     * @throws LexofficeException
     */
    public function get(string $id): array
    {
        /** @var array<string, mixed> $result */
        $result = $this->client->sendRequest('delivery-notes/' . $id);
        return $result;
    }

    /**
     * Lädt die PDF-Datei eines Lieferscheins herunter.
     *
     * @throws LexofficeException
     */
    public function downloadPdf(string $id): string
    {
        return $this->client->downloadFile('delivery-notes/' . $id . '/document');
    }
}
