<?php

declare(strict_types=1);

namespace Four\LexofficeApiClient\DTO;

readonly class Payment
{
    public const STATUS_BALANCED      = 'balanced';
    public const STATUS_OPEN_REVENUE  = 'openRevenue';
    public const STATUS_OPEN_EXPENSE  = 'openExpense';

    /**
     * @param array<mixed> $paymentItems
     */
    public function __construct(
        public readonly float $openAmount,
        public readonly string $paymentStatus,
        public readonly string $voucherType,
        public readonly string $voucherStatus,
        public readonly array $paymentItems,
        public readonly ?string $paidDate = null,
    ) {
    }

    /**
     * @param array<mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            openAmount:    (float) ($data['openAmount'] ?? 0.0),
            paymentStatus: (string) ($data['paymentStatus'] ?? ''),
            voucherType:   (string) ($data['voucherType'] ?? ''),
            voucherStatus: (string) ($data['voucherStatus'] ?? ''),
            paymentItems:  $data['paymentItems'] ?? [],
            paidDate:      $data['paidDate'] ?? null,
        );
    }
}
