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
     * Erstellt eine neue Rechnung (draft oder direkt finalisiert).
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
     * Leitet einen bestehenden Voucher in eine neue Rechnung über (Pursue).
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
     * Gibt eine Rechnung anhand der ID zurück.
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
     * Lädt die PDF-Datei einer Rechnung herunter und gibt die rohen Bytes zurück.
     *
     * @throws LexofficeException
     */
    public function downloadPdf(string $id): string
    {
        return $this->client->downloadFile('invoices/' . $id . '/document');
    }
}
