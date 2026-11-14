<?php

declare(strict_types=1);

namespace Four\LexwareApiClient\Endpoint;

use Four\LexwareApiClient\DTO\DeliveryNote;
use Four\LexwareApiClient\DTO\DownloadedFile;
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
     * Downloads the PDF file of a delivery note and returns the raw bytes.
     *
     * @throws LexwareException
     */
    public function downloadPdf(string $id): string
    {
        return $this->downloadFile($id, 'application/pdf')->content;
    }

    /**
     * Downloads the document file of a delivery note (GET /delivery-notes/{id}/file).
     *
     * @param string $accept Accept header: the wildcard default, application/pdf or application/xml (XRechnung only)
     * @throws LexwareException
     */
    public function downloadFile(string $id, string $accept = '*/*'): DownloadedFile
    {
        return $this->client->download('delivery-notes/' . $id . '/file', $accept);
    }

    /**
     * Pursues an existing sales voucher to a new delivery note.
     *
     * @return array<string, mixed>  {id, resourceUri, version}
     * @throws LexwareException
     */
    public function pursue(string $precedingId, DeliveryNote $deliveryNote): array
    {
        $endpoint = 'delivery-notes?precedingSalesVoucherId=' . rawurlencode($precedingId);

        /** @var array<string, mixed> $result */
        $result = $this->client->sendRequest($endpoint, $deliveryNote->toApiArray(), 'POST');
        return $result;
    }
}
