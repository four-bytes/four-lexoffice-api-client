<?php

declare(strict_types=1);

namespace Four\LexwareApiClient\Endpoint;

use Four\LexwareApiClient\DTO\DownloadedFile;
use Four\LexwareApiClient\DTO\Quotation;
use Four\LexwareApiClient\Exception\LexwareException;
use Four\LexwareApiClient\LexwareClient;

class QuotationEndpoint implements QuotationEndpointInterface
{
    public function __construct(
        private readonly LexwareClient $client,
    ) {
    }

    /**
     * Creates a new quotation.
     *
     * @return array<string, mixed>  {id, resourceUri, version}
     * @throws LexwareException
     */
    public function create(Quotation $quotation, bool $finalize = false): array
    {
        $endpoint = $finalize ? 'quotations?finalize=true' : 'quotations';

        /** @var array<string, mixed> $result */
        $result = $this->client->sendRequest($endpoint, $quotation->toApiArray(), 'POST');
        return $result;
    }

    /**
     * Converts an existing voucher into a new quotation (Pursue).
     *
     * @return array<string, mixed>  {id, resourceUri, version}
     * @throws LexwareException
     * @deprecated The API documents no pursue action to quotations; will be removed in 2.0.
     */
    public function pursue(string $precedingId, Quotation $quotation, bool $finalize = false): array
    {
        $endpoint = 'quotations?precedingSalesVoucherId=' . rawurlencode($precedingId);
        if ($finalize) {
            $endpoint .= '&finalize=true';
        }

        /** @var array<string, mixed> $result */
        $result = $this->client->sendRequest($endpoint, $quotation->toApiArray(), 'POST');
        return $result;
    }

    /**
     * Returns a quotation by ID.
     *
     * @return array<string, mixed>
     * @throws LexwareException
     */
    public function get(string $id): array
    {
        /** @var array<string, mixed> $result */
        $result = $this->client->sendRequest('quotations/' . $id);
        return $result;
    }

    /**
     * Downloads the PDF file of a quotation and returns the raw bytes.
     *
     * @throws LexwareException
     */
    public function downloadPdf(string $id): string
    {
        return $this->downloadFile($id, 'application/pdf')->content;
    }

    /**
     * Downloads the document file of a quotation (GET /quotations/{id}/file).
     *
     * @param string $accept Accept header: the wildcard default, application/pdf or application/xml (XRechnung only)
     * @throws LexwareException
     */
    public function downloadFile(string $id, string $accept = '*/*'): DownloadedFile
    {
        return $this->client->download('quotations/' . $id . '/file', $accept);
    }
}
