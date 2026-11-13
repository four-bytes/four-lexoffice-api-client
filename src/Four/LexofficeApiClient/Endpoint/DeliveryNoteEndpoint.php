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
     * Creates a new delivery note.
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
     * Returns a delivery note by ID.
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
     * Downloads the PDF file of a delivery note.
     *
     * @throws LexofficeException
     */
    public function downloadPdf(string $id): string
    {
        return $this->client->downloadFile('delivery-notes/' . $id . '/document');
    }
}
