<?php

declare(strict_types=1);

namespace Four\LexofficeApiClient\Endpoint;

use Four\LexofficeApiClient\Exception\LexofficeException;

interface FileEndpointInterface
{
    /**
     * Uploads a PDF file for a voucher and returns the file ID.
     *
     * @throws LexofficeException
     */
    public function uploadToVoucher(string $voucherId, string $filePath): string;

    /**
     * Uploads an incoming invoice as a file and returns the file ID.
     *
     * @throws LexofficeException
     */
    public function uploadIncoming(string $filePath): string;
}
