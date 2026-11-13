<?php

declare(strict_types=1);

namespace Four\LexofficeApiClient\Endpoint;

use Four\LexofficeApiClient\DTO\Invoice;
use Four\LexofficeApiClient\Exception\LexofficeException;
use Four\LexofficeApiClient\LexofficeClient;

class InvoiceEndpoint implements InvoiceEndpointInterface
{
    public function __construct(
        private readonly LexofficeClient $client,
    ) {
    }

    /**
     * Creates a new invoice (draft or directly finalized).
     *
     * @return array<string, mixed>  {id, resourceUri, version}
     * @throws LexofficeException
     */
    public function create(Invoice $invoice, bool $finalize = false): array
    {
        $endpoint = $finalize ? 'invoices?finalize=true' : 'invoices';

        /** @var array<string, mixed> $result */
        $result = $this->client->sendRequest($endpoint, $invoice->toApiArray(), 'POST');
        return $result;
    }

    /**
     * Converts an existing voucher into a new invoice (Pursue).
     *
     * @return array<string, mixed>  {id, resourceUri, version}
     * @throws LexofficeException
     */
    public function pursue(string $precedingId, Invoice $invoice, bool $finalize = false): array
    {
        $endpoint = 'invoices?precedingSalesVoucherId=' . rawurlencode($precedingId);
        if ($finalize) {
            $endpoint .= '&finalize=true';
        }

        /** @var array<string, mixed> $result */
        $result = $this->client->sendRequest($endpoint, $invoice->toApiArray(), 'POST');
        return $result;
    }

    /**
     * Returns an invoice by ID.
     *
     * @return array<string, mixed>
     * @throws LexofficeException
     */
    public function get(string $id): array
    {
        /** @var array<string, mixed> $result */
        $result = $this->client->sendRequest('invoices/' . $id);
        return $result;
    }

    /**
     * Downloads the PDF file of an invoice and returns the raw bytes.
     *
     * @throws LexofficeException
     */
    public function downloadPdf(string $id): string
    {
        return $this->client->downloadFile('invoices/' . $id . '/document');
    }
}
