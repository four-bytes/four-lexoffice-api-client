<?php

declare(strict_types=1);

namespace Four\LexofficeApiClient\DTO;

readonly class DeliveryNote
{
    /**
     * @param InvoiceLineItem[] $lineItems
     */
    public function __construct(
        public readonly string $voucherDate,
        public readonly string $addressName,
        public readonly string $addressStreet,
        public readonly string $addressCity,
        public readonly string $addressZip,
        public readonly string $addressCountryCode,
        public readonly array $lineItems,
        public readonly ?string $contactId = null,
        public readonly ?string $introduction = null,
        public readonly ?string $remark = null,
        public readonly ?string $shippingConditionsDate = null,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toApiArray(): array
    {
        $data = [
            'voucherDate' => $this->voucherDate,
            'address'     => [
                'name'        => $this->addressName,
                'street'      => $this->addressStreet,
                'city'        => $this->addressCity,
                'zip'         => $this->addressZip,
                'countryCode' => $this->addressCountryCode,
            ],
            'lineItems' => array_map(
                static fn(InvoiceLineItem $item) => $item->toApiArray(),
                $this->lineItems,
            ),
        ];

        if ($this->contactId !== null) {
            $data['address']['contactId'] = $this->contactId;
        }

        if ($this->introduction !== null) {
            $data['introduction'] = $this->introduction;
        }

        if ($this->remark !== null) {
            $data['remark'] = $this->remark;
        }

        if ($this->shippingConditionsDate !== null) {
            $data['shippingConditions'] = [
                'shippingDate' => $this->shippingConditionsDate,
                'shippingType' => 'delivery',
            ];
        }

        return $data;
    }

    /**
     * Erstellt eine DeliveryNote-Instanz aus einer API-Response.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $address = $data['address'] ?? [];
        $lineItems = array_map(
            static fn(array $item) => InvoiceLineItem::fromArray($item),
            $data['lineItems'] ?? [],
        );

        return new self(
            voucherDate:             $data['voucherDate'] ?? '',
            addressName:             $address['name'] ?? '',
            addressStreet:           $address['street'] ?? '',
            addressCity:             $address['city'] ?? '',
            addressZip:              $address['zip'] ?? '',
            addressCountryCode:      $address['countryCode'] ?? '',
            lineItems:               $lineItems,
            contactId:               $address['contactId'] ?? null,
            introduction:            $data['introduction'] ?? null,
            remark:                  $data['remark'] ?? null,
            shippingConditionsDate:  $data['shippingConditions']['shippingDate'] ?? null,
        );
    }
}
