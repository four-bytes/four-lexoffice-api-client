<?php

declare(strict_types=1);

namespace Four\LexwareApiClient\Endpoint;

use Four\LexwareApiClient\DTO\DeliveryNote;
use Four\LexwareApiClient\Exception\LexwareException;
use Four\LexwareApiClient\LexwareClient;

class DeliveryNoteEndpoint implements DeliveryNoteEndpointInterface
{
    public function __construct(
        private readonly LexwareClient $client,
    ) {
    }

    /**
     * Creates a new delivery note.
     *
     * @return array<string, mixed>  {id, resourceUri, version}
     * @throws LexwareException
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
     * @throws LexwareException
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
     * @throws LexwareException
     */
    public function downloadPdf(string $id): string
    {
        return $this->client->downloadFile('delivery-notes/' . $id . '/document');
    }
}
