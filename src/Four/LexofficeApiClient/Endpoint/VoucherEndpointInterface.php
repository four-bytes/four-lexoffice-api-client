<?php

declare(strict_types=1);

namespace Four\LexofficeApiClient\Endpoint;

use Four\LexofficeApiClient\DTO\Voucher;
use Four\LexofficeApiClient\Exception\LexofficeException;

interface VoucherEndpointInterface
{
    /**
     * Gibt einen Voucher anhand der ID zurück.
     *
     * @return array<string, mixed>
     * @throws LexofficeException
     */
    public function get(string $voucherId): array;

    /**
     * Erstellt einen neuen Voucher (Ausgangsrechnung) und gibt die ID zurück.
     *
     * @throws LexofficeException
     */
    public function create(Voucher $voucher): string;

    /**
     * Aktualisiert einen bestehenden Voucher.
     *
     * @return array<string, mixed>
     * @throws LexofficeException
     */
    public function update(string $voucherId, Voucher $voucher): array;
}
