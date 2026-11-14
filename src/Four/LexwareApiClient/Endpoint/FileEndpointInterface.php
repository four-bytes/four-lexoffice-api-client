<?php

declare(strict_types=1);

namespace Four\LexwareApiClient\Endpoint;

use Four\LexwareApiClient\DTO\DownloadedFile;
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

    /**
     * Downloads a bookkeeping voucher file (GET /files/{id}).
     *
     * Sales voucher documents are downloaded via the downloadFile() method of their endpoint.
     *
     * @param string $accept Accept header: the wildcard default, application/pdf, application/xml, image/jpeg or image/png
     * @throws LexwareException
     */
    public function download(string $fileId, string $accept = '*/*'): DownloadedFile;
}
