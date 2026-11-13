<?php

declare(strict_types=1);

namespace Four\LexofficeApiClient\Endpoint;

use Four\LexofficeApiClient\DTO\Payment;
use Four\LexofficeApiClient\Exception\LexofficeException;

interface PaymentEndpointInterface
{
    /**
     * Gibt den Zahlungsstatus eines Vouchers zurück (read-only).
     *
     * @throws LexofficeException
     */
    public function get(string $voucherId): Payment;
}
