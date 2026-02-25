<?php

declare(strict_types=1);

namespace Four\LexofficeApiClient\Endpoint;

use Four\LexofficeApiClient\DTO\OrderConfirmation;
use Four\LexofficeApiClient\Exception\LexofficeException;
use Four\LexofficeApiClient\LexofficeClient;

class OrderConfirmationEndpoint
{
    public function __construct(
        private readonly LexofficeClient $client,
    ) {
    }

    /**
     * Erstellt eine neue Auftragsbestätigung.
     *
     * @return array<string, mixed>  {id, resourceUri, version}
     * @throws LexofficeException
     */
    public function create(OrderConfirmation $orderConfirmation, bool $finalize = false): array
    {
        $endpoint = $finalize ? 'order-confirmations?finalize=true' : 'order-confirmations';

        /** @var array<string, mixed> $result */
        $result = $this->client->sendRequest($endpoint, $orderConfirmation->toApiArray(), 'POST');
        return $result;
    }

    /**
     * Leitet einen bestehenden Voucher in eine neue Auftragsbestätigung über (Pursue).
     *
     * @return array<string, mixed>  {id, resourceUri, version}
     * @throws LexofficeException
     */
    public function pursue(string $precedingId, OrderConfirmation $orderConfirmation, bool $finalize = false): array
    {
        $endpoint = 'order-confirmations?precedingSalesVoucherId=' . rawurlencode($precedingId);
        if ($finalize) {
            $endpoint .= '&finalize=true';
        }

        /** @var array<string, mixed> $result */
        $result = $this->client->sendRequest($endpoint, $orderConfirmation->toApiArray(), 'POST');
        return $result;
    }

    /**
     * Gibt eine Auftragsbestätigung anhand der ID zurück.
     *
     * @return array<string, mixed>
     * @throws LexofficeException
     */
    public function get(string $id): array
    {
        /** @var array<string, mixed> $result */
        $result = $this->client->sendRequest('order-confirmations/' . $id);
        return $result;
    }

    /**
     * Lädt die PDF-Datei einer Auftragsbestätigung herunter.
     *
     * @throws LexofficeException
     */
    public function downloadPdf(string $id): string
    {
        return $this->client->downloadFile('order-confirmations/' . $id . '/document');
    }
}
