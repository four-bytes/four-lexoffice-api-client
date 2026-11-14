<?php

declare(strict_types=1);

namespace Four\LexwareApiClient\Endpoint;

use Four\LexwareApiClient\DTO\CreditNote;
use Four\LexwareApiClient\DTO\DownloadedFile;
use Four\LexwareApiClient\Exception\LexwareException;
use Four\LexwareApiClient\LexwareClient;

class CreditNoteEndpoint implements CreditNoteEndpointInterface
{
    public function __construct(
        private readonly LexwareClient $client,
    ) {
    }

    /**
     * Creates a new credit note.
     *
     * @return array<string, mixed>  {id, resourceUri, version}
     * @throws LexwareException
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
     * @throws LexwareException
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
     * @throws LexwareException
     */
    public function get(string $id): array
    {
        /** @var array<string, mixed> $result */
        $result = $this->client->sendRequest('credit-notes/' . $id);
        return $result;
    }

    /**
     * Downloads the PDF file of a credit note and returns the raw bytes.
     *
     * @throws LexwareException
     */
    public function downloadPdf(string $id): string
    {
        return $this->downloadFile($id, 'application/pdf')->content;
    }

    /**
     * Downloads the document file of a credit note (GET /credit-notes/{id}/file).
     *
     * @param string $accept Accept header: the wildcard default, application/pdf or application/xml (XRechnung only)
     * @throws LexwareException
     */
    public function downloadFile(string $id, string $accept = '*/*'): DownloadedFile
    {
        return $this->client->download('credit-notes/' . $id . '/file', $accept);
    }
}
