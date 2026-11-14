<?php

declare(strict_types=1);

namespace Four\LexwareApiClient\Endpoint;

use Four\LexwareApiClient\DTO\OrderConfirmation;
use Four\LexwareApiClient\DTO\DownloadedFile;
use Four\LexwareApiClient\Exception\LexwareException;

interface OrderConfirmationEndpointInterface
{
    /**
     * Creates a new order confirmation.
     *
     * @return array<string, mixed>  {id, resourceUri, version}
     * @throws LexwareException
     */
    public function create(OrderConfirmation $orderConfirmation, bool $finalize = false): array;

    /**
     * Converts an existing voucher into a new order confirmation (Pursue).
     *
     * @return array<string, mixed>  {id, resourceUri, version}
     * @throws LexwareException
     */
    public function pursue(string $precedingId, OrderConfirmation $orderConfirmation, bool $finalize = false): array;

    /**
     * Returns an order confirmation by ID.
     *
     * @return array<string, mixed>
     * @throws LexwareException
     */
    public function get(string $id): array;

    /**
     * Downloads the PDF file of an order confirmation and returns the raw bytes.
     *
     * @throws LexwareException
     */
    public function downloadPdf(string $id): string;

    /**
     * Downloads the document file of an order confirmation (GET /order-confirmations/{id}/file).
     *
     * @param string $accept Accept header: the wildcard default, application/pdf or application/xml (XRechnung only)
     * @throws LexwareException
     */
    public function downloadFile(string $id, string $accept = '*/*'): DownloadedFile;
}
