<?php

declare(strict_types=1);

namespace Four\LexwareApiClient\Endpoint;

use Four\LexwareApiClient\DTO\DownloadedFile;
use Four\LexwareApiClient\DTO\Invoice;
use Four\LexwareApiClient\Exception\LexwareException;
use Four\LexwareApiClient\LexwareClient;

class InvoiceEndpoint implements InvoiceEndpointInterface
{
    public function __construct(
        private readonly LexwareClient $client,
    ) {
    }

    /**
     * Creates a new invoice (draft or directly finalized).
     *
     * @return array<string, mixed>  {id, resourceUri, version}
     * @throws LexwareException
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
     * @throws LexwareException
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
     * @throws LexwareException
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
     * @throws LexwareException
     */
    public function downloadPdf(string $id): string
    {
        return $this->downloadFile($id, 'application/pdf')->content;
    }

    /**
     * Downloads the document file of an invoice (GET /invoices/{id}/file).
     *
     * @param string $accept Accept header: the wildcard default, application/pdf or application/xml (XRechnung only)
     * @throws LexwareException
     */
    public function downloadFile(string $id, string $accept = '*/*'): DownloadedFile
    {
        return $this->client->download('invoices/' . $id . '/file', $accept);
    }
}
