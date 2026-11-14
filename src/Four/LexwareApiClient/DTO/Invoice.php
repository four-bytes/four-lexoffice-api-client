<?php

declare(strict_types=1);

namespace Four\LexwareApiClient\DTO;

readonly class Invoice
{
    /**
     * @param InvoiceLineItem[]        $lineItems
     * @param array<string, mixed>|null $totalPrice
     * @param array<string, mixed>|null $xRechnung
     */
    public function __construct(
        public readonly string $voucherDate,
        public readonly string $addressName,
        public readonly string $addressStreet,
        public readonly string $addressCity,
        public readonly string $addressZip,
        public readonly string $addressCountryCode,
        public readonly array $lineItems,
        public readonly string $taxType = 'gross',
        public readonly ?string $contactId = null,
        public readonly ?string $introduction = null,
        public readonly ?string $remark = null,
        public readonly ?string $paymentConditionId = null,
        public readonly ?string $shippingConditionsDate = null,
        public readonly ?array $totalPrice = null,
        public readonly ?string $title = null,
        public readonly ?array $xRechnung = null,
        public readonly ?string $printLayoutId = null,
        public readonly ?string $addressSupplement = null,
        /** Read-only: NONE, EN16931 (ZUGFeRD) or XRechnung. Never sent to the API. */
        public readonly ?string $electronicDocumentProfile = null,
        /** Read-only: true for a closing invoice (Schlussrechnung). Never sent to the API. */
        public readonly ?bool $closingInvoice = null,
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
            'lineItems'      => array_map(
                static fn(InvoiceLineItem $item) => $item->toApiArray(),
                $this->lineItems,
            ),
            'taxConditions' => [
                'taxType' => $this->taxType,
            ],
        ];

        if ($this->contactId !== null) {
            $data['address']['contactId'] = $this->contactId;
        }

        if ($this->addressSupplement !== null) {
            $data['address']['supplement'] = $this->addressSupplement;
        }

        if ($this->introduction !== null) {
            $data['introduction'] = $this->introduction;
        }

        if ($this->remark !== null) {
            $data['remark'] = $this->remark;
        }

        if ($this->paymentConditionId !== null) {
            $data['paymentConditions'] = [
                'paymentTermLabelTemplate' => '',
                'paymentTermDuration'      => 0,
                'id'                       => $this->paymentConditionId,
            ];
        }

        if ($this->shippingConditionsDate !== null) {
            $data['shippingConditions'] = [
                'shippingDate' => $this->shippingConditionsDate,
                'shippingType' => 'delivery',
            ];
        }

        if ($this->totalPrice !== null) {
            $data['totalPrice'] = $this->totalPrice;
        }

        if ($this->title !== null) {
            $data['title'] = $this->title;
        }
        if ($this->xRechnung !== null) {
            $data['xRechnung'] = $this->xRechnung;
        }
        if ($this->printLayoutId !== null) {
            $data['printLayoutId'] = $this->printLayoutId;
        }

        return $data;
    }

    /**
     * Creates an instance of Invoice from an API response.
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
            voucherDate:        $data['voucherDate'] ?? '',
            addressName:        $address['name'] ?? '',
            addressStreet:      $address['street'] ?? '',
            addressCity:        $address['city'] ?? '',
            addressZip:         $address['zip'] ?? '',
            addressCountryCode: $address['countryCode'] ?? '',
            lineItems:          $lineItems,
            taxType:            $data['taxConditions']['taxType'] ?? 'gross',
            contactId:          $address['contactId'] ?? null,
            introduction:       $data['introduction'] ?? null,
            remark:             $data['remark'] ?? null,
            paymentConditionId: $data['paymentConditions']['id'] ?? null,
            shippingConditionsDate: $data['shippingConditions']['shippingDate'] ?? null,
            totalPrice: $data['totalPrice'] ?? null,
            title: $data['title'] ?? null,
            xRechnung: $data['xRechnung'] ?? null,
            printLayoutId: $data['printLayoutId'] ?? null,
            addressSupplement: $address['supplement'] ?? null,
            electronicDocumentProfile: $data['electronicDocumentProfile'] ?? null,
            closingInvoice: $data['closingInvoice'] ?? null,
        );
    }
}
