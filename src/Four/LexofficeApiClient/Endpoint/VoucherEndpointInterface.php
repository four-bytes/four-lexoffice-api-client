<?php

declare(strict_types=1);

namespace Four\LexofficeApiClient\Endpoint;

use Four\LexofficeApiClient\DTO\Voucher;
use Four\LexofficeApiClient\Exception\LexofficeException;

interface VoucherEndpointInterface
{
    /**
     * Returns a voucher by ID.
     *
     * @return array<string, mixed>
     * @throws LexofficeException
     */
    public function get(string $voucherId): array;

    /**
     * Creates a new voucher (outgoing invoice) and returns its ID.
     *
     * @throws LexofficeException
     */
    public function create(Voucher $voucher): string;

    /**
     * Updates an existing voucher.
     *
     * @return array<string, mixed>
     * @throws LexofficeException
     */
    public function update(string $voucherId, Voucher $voucher): array;
}
