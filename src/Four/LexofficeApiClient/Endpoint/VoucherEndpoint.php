<?php

declare(strict_types=1);

namespace Four\LexofficeApiClient\Endpoint;

use Four\LexofficeApiClient\DTO\Voucher;
use Four\LexofficeApiClient\Exception\LexofficeException;
use Four\LexofficeApiClient\LexofficeClient;

class VoucherEndpoint
{
    public function __construct(
        private readonly LexofficeClient $client,
    ) {
    }

    /**
     * Gibt einen Voucher anhand der ID zurück.
     *
     * @return array<string, mixed>
     * @throws LexofficeException
     */
    public function get(string $voucherId): array
    {
        /** @var array<string, mixed> $result */
        $result = $this->client->sendRequest('vouchers/' . $voucherId);
        return $result;
    }

    /**
     * Erstellt einen neuen Voucher (Ausgangsrechnung) und gibt die ID zurück.
     *
     * @throws LexofficeException
     */
    public function create(Voucher $voucher): string
    {
        $response = $this->client->sendRequest('vouchers', $voucher->toApiArray(), 'POST');

        if (!isset($response['id']) || !is_string($response['id'])) {
            throw new LexofficeException(
                message: 'Ungültige API-Antwort: Kein "id" Feld in der Antwort',
                httpStatusCode: 200,
                responseBody: (string) json_encode($response),
            );
        }

        return $response['id'];
    }

    /**
     * Aktualisiert einen bestehenden Voucher.
     *
     * @return array<string, mixed>
     * @throws LexofficeException
     */
    public function update(string $voucherId, Voucher $voucher): array
    {
        /** @var array<string, mixed> $result */
        $result = $this->client->sendRequest('vouchers/' . $voucherId, $voucher->toApiArray(), 'PUT');
        return $result;
    }
}
