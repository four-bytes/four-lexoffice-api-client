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

    /**
     * Creates an instance of Voucher from an API response.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $voucherItems = [];
        if (isset($data['voucherItems']) && is_array($data['voucherItems'])) {
            $voucherItems = array_map(
                static fn(array $item) => VoucherItem::fromArray($item),
                $data['voucherItems'],
            );
        }

        return new self(
            type:             $data['type'] ?? '',
            voucherNumber:    $data['voucherNumber'] ?? '',
            voucherDate:      $data['voucherDate'] ?? '',
            totalGrossAmount: (float) ($data['totalGrossAmount'] ?? 0),
            totalTaxAmount:   (float) ($data['totalTaxAmount'] ?? 0),
            contactId:        $data['contactId'] ?? '',
            remark:           $data['remark'] ?? '',
            shippingDate:     $data['shippingDate'] ?? null,
            taxType:          $data['taxType'] ?? 'gross',
            voucherItems:     $voucherItems,
        );
    }
}
