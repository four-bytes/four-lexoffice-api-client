<?php

declare(strict_types=1);

namespace Four\LexofficeApiClient\DTO;

readonly class PagedResponse
{
    public function __construct(
        public readonly array $content,
        public readonly int $totalElements,
        public readonly int $totalPages,
        public readonly int $number,
        public readonly int $size,
        public readonly bool $first,
        public readonly bool $last,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            content:       $data['content'] ?? [],
            totalElements: (int) ($data['totalElements'] ?? 0),
            totalPages:    (int) ($data['totalPages'] ?? 0),
            number:        (int) ($data['number'] ?? 0),
            size:          (int) ($data['size'] ?? 0),
            first:         (bool) ($data['first'] ?? true),
            last:          (bool) ($data['last'] ?? true),
        );
    }
}
