<?php

declare(strict_types=1);

namespace Four\LexwareApiClient\DTO;

/**
 * A binary file returned by a download endpoint (`/{type}/{id}/file`, `/files/{id}`).
 */
readonly class DownloadedFile
{
    public function __construct(
        public string $content,
        public string $contentType,
        public ?string $fileName = null,
    ) {
    }

    /**
     * Extracts the suggested file name from a Content-Disposition header value.
     */
    public static function parseFileName(string $contentDisposition): ?string
    {
        if (preg_match("/filename\\*\\s*=\\s*(?:UTF-8|utf-8)''([^;]+)/", $contentDisposition, $m) === 1) {
            return rawurldecode(trim($m[1]));
        }

        if (preg_match('/filename\s*=\s*"([^"]*)"/', $contentDisposition, $m) === 1) {
            return $m[1] !== '' ? $m[1] : null;
        }

        if (preg_match('/filename\s*=\s*([^;]+)/', $contentDisposition, $m) === 1) {
            $name = trim($m[1]);
            return $name !== '' ? $name : null;
        }

        return null;
    }
}
