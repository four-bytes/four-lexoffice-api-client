<?php

declare(strict_types=1);

namespace Four\LexofficeApiClient\DTO;

readonly class InvoiceLineItem
{
    public const TYPE_CUSTOM   = 'custom';
    public const TYPE_MATERIAL = 'material';
    public const TYPE_SERVICE  = 'service';
    public const TYPE_TEXT     = 'text';

    public function __construct(
        public readonly string $name,
        public readonly float $quantity,
        public readonly string $unitName,
        public readonly float $unitNetPrice,
        public readonly float $taxRatePercentage,
        public readonly string $type = self::TYPE_CUSTOM,
        public readonly ?string $description = null,
        public readonly ?float $discountPercentage = null,
        public readonly string $currency = 'EUR',
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toApiArray(): array
    {
        $data = [
            'type'      => $this->type,
            'name'      => $this->name,
            'quantity'  => $this->quantity,
            'unitName'  => $this->unitName,
            'unitPrice' => [
                'currency'          => $this->currency,
                'netAmount'         => $this->unitNetPrice,
                'grossAmount'      => round($this->unitNetPrice * (1 + $this->taxRatePercentage / 100.0), 2),
                'taxRatePercentage' => $this->taxRatePercentage,
            ],
        ];

        if ($this->description !== null) {
            $data['description'] = $this->description;
        }

        if ($this->discountPercentage !== null) {
            $data['discountPercentage'] = $this->discountPercentage;
        }

        return $data;
    }

    /**
     * Erstellt eine InvoiceLineItem-Instanz aus einer API-Response.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): static
    {
        $unitPrice = $data['unitPrice'] ?? [];

        return new static(
            name:                 $data['name'] ?? '',
            quantity:             (float) ($data['quantity'] ?? 1),
            unitName:             $data['unitName'] ?? '',
            unitNetPrice:         (float) ($unitPrice['netAmount'] ?? 0),
            taxRatePercentage:    (float) ($unitPrice['taxRatePercentage'] ?? 0),
            type:                 $data['type'] ?? self::TYPE_CUSTOM,
            description:          $data['description'] ?? null,
            discountPercentage:  isset($data['discountPercentage']) ? (float) $data['discountPercentage'] : null,
            currency:             $unitPrice['currency'] ?? 'EUR',
        );
    }
}
