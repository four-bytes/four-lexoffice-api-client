<?php

declare(strict_types=1);

namespace Four\LexwareApiClient\Endpoint;

use Four\LexwareApiClient\Exception\LexwareException;

interface FileEndpointInterface
{
    /**
     * Uploads a PDF file for a voucher and returns the file ID.
     *
     * @throws LexwareException
     */
    public function uploadToVoucher(string $voucherId, string $filePath): string;

    /**
     * Uploads an incoming invoice as a file and returns the file ID.
     *
     * @throws LexwareException
     */
    public function uploadIncoming(string $filePath): string;
}
