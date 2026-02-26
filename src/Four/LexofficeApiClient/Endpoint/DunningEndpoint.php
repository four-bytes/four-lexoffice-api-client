<?php

declare(strict_types=1);

namespace Four\LexofficeApiClient\Endpoint;

use Four\LexofficeApiClient\DTO\Dunning;
use Four\LexofficeApiClient\Exception\LexofficeException;
use Four\LexofficeApiClient\LexofficeClient;

class DunningEndpoint
{
    public function __construct(
        private readonly LexofficeClient $client,
    ) {
    }

    /**
     * Erstellt eine neue Mahnung (draft oder direkt finalisiert).
     *
     * @return array<string, mixed>  {id, resourceUri, version}
     * @throws LexofficeException
     */
    public function create(Dunning $dunning, bool $finalize = false): array
    {
        $endpoint = $finalize ? 'dunnings?finalize=true' : 'dunnings';

        /** @var array<string, mixed> $result */
        $result = $this->client->sendRequest($endpoint, $dunning->toApiArray(), 'POST');
        return $result;
    }

    /**
     * Leitet einen bestehenden Voucher in eine neue Mahnung über (Pursue).
     *
     * @return array<string, mixed>  {id, resourceUri, version}
     * @throws LexofficeException
     */
    public function pursue(string $precedingId, Dunning $dunning, bool $finalize = false): array
    {
        $endpoint = 'dunnings?precedingSalesVoucherId=' . rawurlencode($precedingId);
        if ($finalize) {
            $endpoint .= '&finalize=true';
        }

        /** @var array<string, mixed> $result */
        $result = $this->client->sendRequest($endpoint, $dunning->toApiArray(), 'POST');
        return $result;
    }

    /**
     * Gibt eine Mahnung anhand der ID zurück.
     *
     * @return array<string, mixed>
     * @throws LexofficeException
     */
    public function get(string $id): array
    {
        /** @var array<string, mixed> $result */
        $result = $this->client->sendRequest('dunnings/' . $id);
        return $result;
    }

    /**
     * Lädt die PDF-Datei einer Mahnung herunter und gibt die rohen Bytes zurück.
     *
     * @throws LexofficeException
     */
    public function downloadPdf(string $id): string
    {
        return $this->client->downloadFile('dunnings/' . $id . '/document');
    }
}
