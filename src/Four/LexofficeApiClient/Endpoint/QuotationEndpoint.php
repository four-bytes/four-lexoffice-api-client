<?php

declare(strict_types=1);

namespace Four\LexofficeApiClient\Endpoint;

use Four\LexofficeApiClient\DTO\Quotation;
use Four\LexofficeApiClient\Exception\LexofficeException;
use Four\LexofficeApiClient\LexofficeClient;

class QuotationEndpoint implements QuotationEndpointInterface
{
    public function __construct(
        private readonly LexofficeClient $client,
    ) {
    }

    /**
     * Creates a new quotation.
     *
     * @return array<string, mixed>  {id, resourceUri, version}
     * @throws LexofficeException
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
     * @throws LexofficeException
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
     * @throws LexofficeException
     */
    public function get(string $id): array
    {
        /** @var array<string, mixed> $result */
        $result = $this->client->sendRequest('quotations/' . $id);
        return $result;
    }

    /**
     * Downloads the PDF file of a quotation.
     *
     * @throws LexofficeException
     */
    public function downloadPdf(string $id): string
    {
        return $this->client->downloadFile('quotations/' . $id . '/document');
    }
}
