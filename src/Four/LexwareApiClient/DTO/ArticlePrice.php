<?php

declare(strict_types=1);

namespace Four\LexwareApiClient\DTO;

readonly class ArticlePrice
{
    public function __construct(
        public readonly float $netPrice,
        public readonly string $leadingPrice = 'NET',   // NET|GROSS
        public readonly int $taxRate = 19,
        public readonly ?float $grossPrice = null,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toApiArray(): array
    {
        $data = [
            'netPrice'     => $this->netPrice,
            'leadingPrice' => $this->leadingPrice,
            'taxRate'      => $this->taxRate,
        ];

        if ($this->grossPrice !== null) {
            $data['grossPrice'] = $this->grossPrice;
        }

        return $data;
    }
}
