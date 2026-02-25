<?php

declare(strict_types=1);

namespace Four\LexofficeApiClient\DTO;

readonly class Article
{
    public const TYPE_PRODUCT = 'PRODUCT';
    public const TYPE_SERVICE = 'SERVICE';

    public function __construct(
        public readonly string $title,
        public readonly string $type,           // PRODUCT|SERVICE
        public readonly string $unitName,
        public readonly float $netPrice,
        public readonly string $leadingPrice = 'NET',   // NET|GROSS
        public readonly int $taxRate = 19,
        public readonly ?string $description = null,
        public readonly ?string $articleNumber = null,
        public readonly ?string $gtin = null,
        public readonly ?string $note = null,
        public readonly int $version = 0,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toApiArray(): array
    {
        $data = [
            'version'  => $this->version,
            'title'    => $this->title,
            'type'     => $this->type,
            'unitName' => $this->unitName,
            'price'    => [
                'netPrice'     => $this->netPrice,
                'leadingPrice' => $this->leadingPrice,
                'taxRate'      => $this->taxRate,
            ],
        ];

        if ($this->description !== null) {
            $data['description'] = $this->description;
        }

        if ($this->articleNumber !== null) {
            $data['articleNumber'] = $this->articleNumber;
        }

        if ($this->gtin !== null) {
            $data['gtin'] = $this->gtin;
        }

        if ($this->note !== null) {
            $data['note'] = $this->note;
        }

        return $data;
    }
}
