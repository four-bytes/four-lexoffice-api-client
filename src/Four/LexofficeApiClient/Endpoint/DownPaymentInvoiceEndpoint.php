<?php

declare(strict_types=1);

namespace Four\LexofficeApiClient\Endpoint;

use Four\LexofficeApiClient\Exception\LexofficeException;
use Four\LexofficeApiClient\LexofficeClient;

class DownPaymentInvoiceEndpoint implements DownPaymentInvoiceEndpointInterface
{
    public function __construct(
        private readonly LexofficeClient $client,
    ) {
    }

    /**
     * Returns a down payment invoice by ID.
     *
     * @return array<string, mixed>
     * @throws LexofficeException
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
     * @throws LexofficeException
     */
    public function downloadPdf(string $id): string
    {
        return $this->client->downloadFile('down-payment-invoices/' . $id . '/document');
    }
}
