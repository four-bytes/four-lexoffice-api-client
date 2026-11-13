<?php

declare(strict_types=1);

namespace Four\LexofficeApiClient\Endpoint;

use Four\LexofficeApiClient\DTO\Payment;
use Four\LexofficeApiClient\Exception\LexofficeException;
use Four\LexofficeApiClient\LexofficeClient;

class PaymentEndpoint implements PaymentEndpointInterface
{
    public function __construct(
        private readonly LexofficeClient $client,
    ) {
    }

    /**
     * Returns the payment status of a voucher (read-only).
     *
     * @throws LexofficeException
     */
    public function get(string $voucherId): Payment
    {
        $data = $this->client->sendRequest('payments/' . $voucherId);
        return Payment::fromArray($data);
    }
}
