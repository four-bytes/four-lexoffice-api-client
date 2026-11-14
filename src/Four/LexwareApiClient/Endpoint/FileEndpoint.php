<?php

declare(strict_types=1);

namespace Four\LexwareApiClient\Endpoint;

use Four\LexwareApiClient\Exception\LexwareException;
use Four\LexwareApiClient\LexwareClient;

class FileEndpoint implements FileEndpointInterface
{
    public function __construct(
        private readonly LexwareClient $client,
    ) {
    }

    /**
     * Uploads a PDF file for a voucher and returns the file ID.
     *
     * @throws LexwareException
     */
    public function uploadToVoucher(string $voucherId, string $filePath): string
    {
        $response = $this->client->uploadFile(
            endpoint: 'vouchers/' . $voucherId . '/files',
            filePath: $filePath,
            type: 'voucher',
        );

        if (!isset($response['id']) || !is_string($response['id'])) {
            throw new LexwareException(
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
     * @throws LexwareException
     */
    public function uploadIncoming(string $filePath): string
    {
        $response = $this->client->uploadFile(
            endpoint: 'files',
            filePath: $filePath,
            type: 'voucher',
        );

        if (!isset($response['id']) || !is_string($response['id'])) {
            throw new LexwareException(
                message: 'Ungültige API-Antwort beim Eingangsrechnung-Upload: Kein "id" Feld',
                httpStatusCode: 200,
                responseBody: (string) json_encode($response),
            );
        }

        return $response['id'];
    }
}
