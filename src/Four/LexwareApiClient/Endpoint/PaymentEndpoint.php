<?php

declare(strict_types=1);

namespace Four\LexwareApiClient\Endpoint;

use Four\LexwareApiClient\DTO\Payment;
use Four\LexwareApiClient\Exception\LexwareException;
use Four\LexwareApiClient\LexwareClient;

class PaymentEndpoint implements PaymentEndpointInterface
{
    public function __construct(
        private readonly LexwareClient $client,
    ) {
    }

    /**
     * Returns the payment status of a voucher (read-only).
     *
     * @throws LexwareException
     */
    public function get(string $voucherId): Payment
    {
        $data = $this->client->sendRequest('payments/' . $voucherId);
        return Payment::fromArray($data);
    }
}
