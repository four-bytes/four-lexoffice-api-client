<?php

declare(strict_types=1);

namespace Four\LexofficeApiClient\Endpoint;

use Four\LexofficeApiClient\Exception\LexofficeException;

interface FileEndpointInterface
{
    /**
     * Lädt eine PDF-Datei zu einem Voucher hoch und gibt die Datei-ID zurück.
     *
     * @throws LexofficeException
     */
    public function uploadToVoucher(string $voucherId, string $filePath): string;

    /**
     * Lädt eine Eingangsrechnung als Datei hoch und gibt die Datei-ID zurück.
     *
     * @throws LexofficeException
     */
    public function uploadIncoming(string $filePath): string;
}
