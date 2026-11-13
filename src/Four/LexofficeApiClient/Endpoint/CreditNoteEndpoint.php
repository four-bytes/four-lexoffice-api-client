<?php

declare(strict_types=1);

namespace Four\LexofficeApiClient\Endpoint;

use Four\LexofficeApiClient\DTO\CreditNote;
use Four\LexofficeApiClient\Exception\LexofficeException;
use Four\LexofficeApiClient\LexofficeClient;

class CreditNoteEndpoint implements CreditNoteEndpointInterface
{
    public function __construct(
        private readonly LexofficeClient $client,
    ) {
    }

    /**
     * Creates a new credit note.
     *
     * @return array<string, mixed>  {id, resourceUri, version}
     * @throws LexofficeException
     */
    public function create(CreditNote $creditNote, bool $finalize = false): array
    {
        $endpoint = $finalize ? 'credit-notes?finalize=true' : 'credit-notes';

        /** @var array<string, mixed> $result */
        $result = $this->client->sendRequest($endpoint, $creditNote->toApiArray(), 'POST');
        return $result;
    }

    /**
     * Converts an existing voucher into a new credit note (Pursue).
     *
     * @return array<string, mixed>  {id, resourceUri, version}
     * @throws LexofficeException
     */
    public function pursue(string $precedingId, CreditNote $creditNote, bool $finalize = false): array
    {
        $endpoint = 'credit-notes?precedingSalesVoucherId=' . rawurlencode($precedingId);
        if ($finalize) {
            $endpoint .= '&finalize=true';
        }

        /** @var array<string, mixed> $result */
        $result = $this->client->sendRequest($endpoint, $creditNote->toApiArray(), 'POST');
        return $result;
    }

    /**
     * Returns a credit note by ID.
     *
     * @return array<string, mixed>
     * @throws LexofficeException
     */
    public function get(string $id): array
    {
        /** @var array<string, mixed> $result */
        $result = $this->client->sendRequest('credit-notes/' . $id);
        return $result;
    }

    /**
     * Downloads the PDF file of a credit note.
     *
     * @throws LexofficeException
     */
    public function downloadPdf(string $id): string
    {
        return $this->client->downloadFile('credit-notes/' . $id . '/document');
    }
}
