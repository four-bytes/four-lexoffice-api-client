<?php

declare(strict_types=1);

namespace Four\LexwareApiClient\DTO;

readonly class FileUpload
{
    public function __construct(
        public string $filePath,
        public string $type = 'voucher',
        public ?string $voucherId = null,
    ) {
    }
}
