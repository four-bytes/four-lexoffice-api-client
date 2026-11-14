<?php

declare(strict_types=1);

namespace Four\LexwareApiClient\Endpoint;

use Four\LexwareApiClient\DTO\Payment;
use Four\LexwareApiClient\Exception\LexwareException;

interface PaymentEndpointInterface
{
    /**
     * Returns the payment status of a voucher (read-only).
     *
     * @throws LexwareException
     */
    public function get(string $voucherId): Payment;
}
