<?php

declare(strict_types=1);

namespace Four\LexwareApiClient\Endpoint;

use Four\LexwareApiClient\DTO\Voucher;
use Four\LexwareApiClient\Exception\LexwareException;

interface VoucherEndpointInterface
{
    /**
     * Returns a voucher by ID.
     *
     * @return array<string, mixed>
     * @throws LexwareException
     */
    public function get(string $voucherId): array;

    /**
     * Creates a new voucher (outgoing invoice) and returns its ID.
     *
     * @throws LexwareException
     */
    public function create(Voucher $voucher): string;

    /**
     * Updates an existing voucher.
     *
     * @return array<string, mixed>
     * @throws LexwareException
     */
    public function update(string $voucherId, Voucher $voucher): array;
}
