<?php

declare(strict_types=1);

namespace Four\LexofficeApiClient\Endpoint;

use Four\LexofficeApiClient\Exception\LexofficeException;

interface DownPaymentInvoiceEndpointInterface
{
    /**
     * Gibt eine Abschlagsrechnung anhand der ID zurück.
     *
     * @return array<string, mixed>
     * @throws LexofficeException
     */
    public function get(string $id): array;

    /**
     * Lädt die PDF-Datei einer Abschlagsrechnung herunter und gibt die rohen Bytes zurück.
     *
     * @throws LexofficeException
     */
    public function downloadPdf(string $id): string;
}
