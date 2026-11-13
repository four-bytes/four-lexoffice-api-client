<?php

declare(strict_types=1);

namespace Four\LexofficeApiClient\Endpoint;

use Four\LexofficeApiClient\Exception\LexofficeException;
use Four\LexofficeApiClient\LexofficeClient;

class FileEndpoint implements FileEndpointInterface
{
    public function __construct(
        private readonly LexofficeClient $client,
    ) {
    }

    /**
     * Uploads a PDF file for a voucher and returns the file ID.
     *
     * @throws LexofficeException
     */
    public function uploadToVoucher(string $voucherId, string $filePath): string
    {
        $response = $this->client->uploadFile(
            endpoint: 'vouchers/' . $voucherId . '/files',
            filePath: $filePath,
            type: 'voucher',
        );

        if (!isset($response['id']) || !is_string($response['id'])) {
            throw new LexofficeException(
                message: 'Ungültige API-Antwort beim Datei-Upload: Kein "id" Feld',
                httpStatusCode: 200,
                responseBody: (string) json_encode($response),
            );
        }

        return $response['id'];
    }

    /**
     * Uploads an incoming invoice as a file and returns the file ID.
     *
     * @throws LexofficeException
     */
    public function uploadIncoming(string $filePath): string
    {
        $response = $this->client->uploadFile(
            endpoint: 'files',
            filePath: $filePath,
            type: 'voucher',
        );

        if (!isset($response['id']) || !is_string($response['id'])) {
            throw new LexofficeException(
                message: 'Ungültige API-Antwort beim Eingangsrechnung-Upload: Kein "id" Feld',
                httpStatusCode: 200,
                responseBody: (string) json_encode($response),
            );
        }

        return $response['id'];
    }
}
