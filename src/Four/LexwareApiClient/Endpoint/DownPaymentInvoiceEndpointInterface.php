<?php

declare(strict_types=1);

namespace Four\LexwareApiClient\Endpoint;

use Four\LexwareApiClient\Exception\LexwareException;

interface DownPaymentInvoiceEndpointInterface
{
    /**
     * Returns a down payment invoice by ID.
     *
     * @return array<string, mixed>
     * @throws LexwareException
     */
    public function get(string $id): array;

    /**
     * Downloads the PDF file of a down payment invoice and returns the raw bytes.
     *
     * @throws LexwareException
     */
    public function downloadPdf(string $id): string;
}
