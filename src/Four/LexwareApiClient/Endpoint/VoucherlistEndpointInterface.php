<?php

declare(strict_types=1);

namespace Four\LexwareApiClient\Endpoint;

use Four\LexwareApiClient\DTO\PagedResponse;
use Four\LexwareApiClient\Exception\LexwareException;

interface VoucherlistEndpointInterface
{
    /**
     * Lists vouchers filtered and paginated.
     *
     * @param array<string, string> $filters  Erlaubt: voucherType, voucherStatus, contactId,
     *                                        voucherDateFrom (yyyy-MM-dd), voucherDateTo (yyyy-MM-dd), archived
     * @throws LexwareException
     */
    public function list(array $filters = [], int $page = 0): PagedResponse;
}
