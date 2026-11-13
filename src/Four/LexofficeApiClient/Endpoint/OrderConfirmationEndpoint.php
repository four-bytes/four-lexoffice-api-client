<?php

declare(strict_types=1);

namespace Four\LexofficeApiClient\Endpoint;

use Four\LexofficeApiClient\DTO\OrderConfirmation;
use Four\LexofficeApiClient\Exception\LexofficeException;
use Four\LexofficeApiClient\LexofficeClient;

class OrderConfirmationEndpoint implements OrderConfirmationEndpointInterface
{
    public function __construct(
        private readonly LexofficeClient $client,
    ) {
    }

    /**
     * Creates a new order confirmation.
     *
     * @return array<string, mixed>  {id, resourceUri, version}
     * @throws LexofficeException
     */
    public function create(OrderConfirmation $orderConfirmation, bool $finalize = false): array
    {
        $endpoint = $finalize ? 'order-confirmations?finalize=true' : 'order-confirmations';

        /** @var array<string, mixed> $result */
        $result = $this->client->sendRequest($endpoint, $orderConfirmation->toApiArray(), 'POST');
        return $result;
    }

    /**
     * Converts an existing voucher into a new order confirmation (Pursue).
     *
     * @return array<string, mixed>  {id, resourceUri, version}
     * @throws LexofficeException
     */
    public function pursue(string $precedingId, OrderConfirmation $orderConfirmation, bool $finalize = false): array
    {
        $endpoint = 'order-confirmations?precedingSalesVoucherId=' . rawurlencode($precedingId);
        if ($finalize) {
            $endpoint .= '&finalize=true';
        }

        /** @var array<string, mixed> $result */
        $result = $this->client->sendRequest($endpoint, $orderConfirmation->toApiArray(), 'POST');
        return $result;
    }

    /**
     * Returns an order confirmation by ID.
     *
     * @return array<string, mixed>
     * @throws LexofficeException
     */
    public function get(string $id): array
    {
        /** @var array<string, mixed> $result */
        $result = $this->client->sendRequest('order-confirmations/' . $id);
        return $result;
    }

    /**
     * Downloads the PDF file of an order confirmation.
     *
     * @throws LexofficeException
     */
    public function downloadPdf(string $id): string
    {
        return $this->client->downloadFile('order-confirmations/' . $id . '/document');
    }
}
