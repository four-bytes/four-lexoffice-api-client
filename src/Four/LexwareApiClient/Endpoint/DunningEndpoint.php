<?php

declare(strict_types=1);

namespace Four\LexwareApiClient\Endpoint;

use Four\LexwareApiClient\DTO\DownloadedFile;
use Four\LexwareApiClient\DTO\Dunning;
use Four\LexwareApiClient\Exception\LexwareException;
use Four\LexwareApiClient\LexwareClient;

class DunningEndpoint implements DunningEndpointInterface
{
    public function __construct(
        private readonly LexwareClient $client,
    ) {
    }

    /**
     * Creates a dunning for an invoice or down payment invoice (POST /dunnings?precedingSalesVoucherId={id}).
     *
     * Dunnings are always created as drafts.
     *
     * @param string $precedingSalesVoucherId ID of the invoice to be dunned (mandatory per API docs)
     * @return array<string, mixed>  {id, resourceUri, version}
     * @throws LexwareException
     */
    public function create(Dunning $dunning, string $precedingSalesVoucherId): array
    {
        $endpoint = 'dunnings?precedingSalesVoucherId=' . rawurlencode($precedingSalesVoucherId);

        /** @var array<string, mixed> $result */
        $result = $this->client->sendRequest($endpoint, $dunning->toApiArray(), 'POST');
        return $result;
    }

    /**
     * Pursues an invoice to a new dunning — same API call as create().
     *
     * @param bool $finalize Deprecated: not documented for dunnings (always created as drafts); will be removed in 2.0.
     * @return array<string, mixed>  {id, resourceUri, version}
     * @throws LexwareException
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
     * @throws LexwareException
     */
    public function get(string $id): array
    {
        /** @var array<string, mixed> $result */
        $result = $this->client->sendRequest('dunnings/' . $id);
        return $result;
    }

    /**
     * Downloads the PDF file of a dunning and returns the raw bytes.
     *
     * @throws LexwareException
     */
    public function downloadPdf(string $id): string
    {
        return $this->downloadFile($id, 'application/pdf')->content;
    }

    /**
     * Downloads the document file of a dunning (GET /dunnings/{id}/file).
     *
     * @param string $accept Accept header: the wildcard default, application/pdf or application/xml (XRechnung only)
     * @throws LexwareException
     */
    public function downloadFile(string $id, string $accept = '*/*'): DownloadedFile
    {
        return $this->client->download('dunnings/' . $id . '/file', $accept);
    }
}
