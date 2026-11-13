<?php

declare(strict_types=1);

namespace Four\LexofficeApiClient\Endpoint;

use Four\LexofficeApiClient\Exception\LexofficeException;

interface DownPaymentInvoiceEndpointInterface
{
    /**
     * Returns a down payment invoice by ID.
     *
     * @return array<string, mixed>
     * @throws LexofficeException
     */
    public function get(string $id): array;

    /**
     * Downloads the PDF file of a down payment invoice and returns the raw bytes.
     *
     * @throws LexofficeException
     */
    public function downloadPdf(string $id): string;
}
