<?php

declare(strict_types=1);

namespace Four\LexofficeApiClient\Endpoint;

use Four\LexofficeApiClient\Exception\LexofficeException;
use Four\LexofficeApiClient\LexofficeClient;

class DownPaymentInvoiceEndpoint
{
    public function __construct(
        private readonly LexofficeClient $client,
    ) {
    }

    /**
     * Gibt eine Abschlagsrechnung anhand der ID zurück.
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
     * Lädt die PDF-Datei einer Abschlagsrechnung herunter und gibt die rohen Bytes zurück.
     *
     * @throws LexofficeException
     */
    public function downloadPdf(string $id): string
    {
        return $this->client->downloadFile('down-payment-invoices/' . $id . '/document');
    }
}
