<?php

declare(strict_types=1);

namespace Four\LexwareApiClient\Endpoint;

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
        return $this->client->downloadFile('down-payment-invoices/' . $id . '/document');
    }
}
