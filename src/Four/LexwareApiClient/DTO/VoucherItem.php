<?php

declare(strict_types=1);

namespace Four\LexwareApiClient\DTO;

readonly class VoucherItem
{
    // Bekannte lexware Kategorie-IDs
    public const CATEGORY_INLAND_SALES     = '8f8664a8-fd86-11e1-a21f-0800200c9a66';
    public const CATEGORY_EU_SALES         = '4ebd965a-7126-416c-9d8c-a5c9366ee473';
    public const CATEGORY_EU_DEALER_VAT_FREE = '9075a4e3-66de-4795-a016-3889feca0d20';
    public const CATEGORY_WORLD_EXPORT     = '93d24c20-ea84-424e-a731-5e1b78d1e6a9';
    public const CATEGORY_SERVICE_FEES     = '8f8664a7-fd86-11e1-a21f-0800200c9a66';
    public const CATEGORY_REVENUE          = '8f8664a1-fd86-11e1-a21f-0800200c9a66';

    // Umsatz
    public const CATEGORY_SERVICES                  = '8f8664a0-fd86-11e1-a21f-0800200c9a66';
    // Distance Sales
    public const CATEGORY_DISTANCE_SALES_EU         = '7c112b66-0565-479c-bc18-5845e080880a';
    public const CATEGORY_DISTANCE_SALES_EU_TAXABLE = '4ebd965a-7126-416c-9d8c-a5c9366ee473';
    // Drittland
    public const CATEGORY_THIRD_COUNTRY_SERVICE     = 'ef5b1a6e-f690-4004-9a19-91276348894f';
    // Kleinunternehmer
    public const CATEGORY_SMALL_BUSINESS            = '7a1efa0e-6283-4cbf-9583-8e88d3ba5960';

    public function __construct(
        public float $amount,
        public float $taxAmount,
        public float $taxRatePercent,
        public string $categoryId = self::CATEGORY_INLAND_SALES,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toApiArray(): array
    {
        return [
            'amount'         => number_format($this->amount, 2, '.', ''),
            'taxAmount'      => number_format($this->taxAmount, 2, '.', ''),
            'taxRatePercent' => $this->taxRatePercent == (int)$this->taxRatePercent
                ? (int)$this->taxRatePercent
                : $this->taxRatePercent,
            'categoryId'     => $this->categoryId,
        ];
    }

    /**
     * Creates an instance of VoucherItem from an API response.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            amount:         (float) ($data['amount'] ?? 0),
            taxAmount:      (float) ($data['taxAmount'] ?? 0),
            taxRatePercent: (float) ($data['taxRatePercent'] ?? 0),
            categoryId:     $data['categoryId'] ?? self::CATEGORY_INLAND_SALES,
        );
    }
}
