<?php

declare(strict_types=1);

namespace Four\LexofficeApiClient\DTO;

readonly class Voucher
{
    /**
     * @param VoucherItem[] $voucherItems
     */
    public function __construct(
        public string $type,
        public string $voucherNumber,
        public string $voucherDate,
        public float $totalGrossAmount,
        public float $totalTaxAmount,
        public string $contactId,
        public string $remark = '',
        public ?string $shippingDate = null,
        public string $taxType = 'gross',
        public array $voucherItems = [],
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toApiArray(): array
    {
        $data = [
            'type'             => $this->type,
            'voucherNumber'    => $this->voucherNumber,
            'voucherDate'      => $this->voucherDate,
            'totalGrossAmount' => number_format($this->totalGrossAmount, 2, '.', ''),
            'totalTaxAmount'   => number_format($this->totalTaxAmount, 2, '.', ''),
            'taxType'          => $this->taxType,
            'useCollectiveContact' => false,
            'contactId'        => $this->contactId,
            'voucherItems'     => array_map(
                static fn(VoucherItem $item) => $item->toApiArray(),
                $this->voucherItems,
            ),
        ];

        if ($this->remark !== '') {
            $data['remark'] = $this->remark;
        }

        if ($this->shippingDate !== null) {
            $data['shippingDate'] = $this->shippingDate;
        }

        return $data;
    }
}
