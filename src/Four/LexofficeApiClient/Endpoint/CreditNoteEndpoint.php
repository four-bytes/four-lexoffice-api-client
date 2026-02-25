<?php

declare(strict_types=1);

namespace Four\LexofficeApiClient\Endpoint;

use Four\LexofficeApiClient\DTO\CreditNote;
use Four\LexofficeApiClient\Exception\LexofficeException;
use Four\LexofficeApiClient\LexofficeClient;

class CreditNoteEndpoint
{
    public function __construct(
        private readonly LexofficeClient $client,
    ) {
    }

    /**
     * Erstellt eine neue Gutschrift.
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
     * Leitet einen bestehenden Voucher in eine neue Gutschrift über (Pursue).
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
     * Gibt eine Gutschrift anhand der ID zurück.
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
     * Lädt die PDF-Datei einer Gutschrift herunter.
     *
     * @throws LexofficeException
     */
    public function downloadPdf(string $id): string
    {
        return $this->client->downloadFile('credit-notes/' . $id . '/document');
    }
}
