<?php

declare(strict_types=1);

namespace Four\LexwareApiClient\Endpoint;

use Four\LexwareApiClient\DTO\DownloadedFile;
use Four\LexwareApiClient\Exception\LexwareException;
use Four\LexwareApiClient\LexwareClient;

class DownPaymentInvoiceEndpoint implements DownPaymentInvoiceEndpointInterface
{
    public function __construct(
        private readonly LexwareClient $client,
    ) {
    }

    /**
     * Returns a down payment invoice by ID.
     *
     * @return array<string, mixed>
     * @throws LexwareException
     */
    public function get(string $id): array
    {
        /** @var array<string, mixed> $result */
        $result = $this->client->sendRequest('down-payment-invoices/' . $id);
        return $result;
    }

    /**
     * Downloads the PDF file of a down payment invoice and returns the raw bytes.
     *
     * @throws LexwareException
     */
    public function downloadPdf(string $id): string
    {
        return $this->downloadFile($id, 'application/pdf')->content;
    }

    /**
     * Downloads the document file of a down payment invoice (GET /down-payment-invoices/{id}/file).
     *
     * @param string $accept Accept header: the wildcard default, application/pdf or application/xml (XRechnung only)
     * @throws LexwareException
     */
    public function downloadFile(string $id, string $accept = '*/*'): DownloadedFile
    {
        return $this->client->download('down-payment-invoices/' . $id . '/file', $accept);
    }
}
