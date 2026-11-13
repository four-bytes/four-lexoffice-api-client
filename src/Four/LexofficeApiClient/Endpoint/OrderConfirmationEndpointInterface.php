<?php

declare(strict_types=1);

namespace Four\LexofficeApiClient\Endpoint;

use Four\LexofficeApiClient\DTO\OrderConfirmation;
use Four\LexofficeApiClient\Exception\LexofficeException;

interface OrderConfirmationEndpointInterface
{
    /**
     * Creates a new order confirmation.
     *
     * @return array<string, mixed>  {id, resourceUri, version}
     * @throws LexofficeException
     */
    public function create(OrderConfirmation $orderConfirmation, bool $finalize = false): array;

    /**
     * Converts an existing voucher into a new order confirmation (Pursue).
     *
     * @return array<string, mixed>  {id, resourceUri, version}
     * @throws LexofficeException
     */
    public function pursue(string $precedingId, OrderConfirmation $orderConfirmation, bool $finalize = false): array;

    /**
     * Returns an order confirmation by ID.
     *
     * @return array<string, mixed>
     * @throws LexofficeException
     */
    public function get(string $id): array;

    /**
     * Downloads the PDF file of an order confirmation.
     *
     * @throws LexofficeException
     */
    public function downloadPdf(string $id): string;
}
