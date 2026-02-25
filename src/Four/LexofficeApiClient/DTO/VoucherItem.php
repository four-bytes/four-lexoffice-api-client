<?php

declare(strict_types=1);

namespace Four\LexofficeApiClient\DTO;

readonly class VoucherItem
{
    // Bekannte lexoffice Kategorie-IDs
    public const CATEGORY_INLAND_SALES     = '8f8664a8-fd86-11e1-a21f-0800200c9a66';
    public const CATEGORY_EU_SALES         = '4ebd965a-7126-416c-9d8c-a5c9366ee473';
    public const CATEGORY_EU_DEALER_VAT_FREE = '9075a4e3-66de-4795-a016-3889feca0d20';
    public const CATEGORY_WORLD_EXPORT     = '93d24c20-ea84-424e-a731-5e1b78d1e6a9';
    public const CATEGORY_SERVICE_FEES     = '8f8664a7-fd86-11e1-a21f-0800200c9a66';
    public const CATEGORY_REVENUE          = '8f8664a1-fd86-11e1-a21f-0800200c9a66';

    public function __construct(
        public float $amount,
        public float $taxAmount,
        public int $taxRatePercent,
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
            'taxRatePercent' => $this->taxRatePercent,
            'categoryId'     => $this->categoryId,
        ];
    }
}
