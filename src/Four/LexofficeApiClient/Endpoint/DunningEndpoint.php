<?php

declare(strict_types=1);

namespace Four\LexofficeApiClient\Endpoint;

use Four\LexofficeApiClient\DTO\Dunning;
use Four\LexofficeApiClient\Exception\LexofficeException;
use Four\LexofficeApiClient\LexofficeClient;

class DunningEndpoint implements DunningEndpointInterface
{
    public function __construct(
        private readonly LexofficeClient $client,
    ) {
    }

    /**
     * Creates a new dunning notice (draft or directly finalized).
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
     * Converts an existing voucher into a new dunning notice (Pursue).
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
     * Returns a dunning notice by ID.
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
     * Downloads the PDF file of a dunning notice and returns the raw bytes.
     *
     * @throws LexofficeException
     */
    public function downloadPdf(string $id): string
    {
        return $this->client->downloadFile('dunnings/' . $id . '/document');
    }
}
