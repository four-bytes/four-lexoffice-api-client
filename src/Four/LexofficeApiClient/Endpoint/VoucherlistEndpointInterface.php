<?php

declare(strict_types=1);

namespace Four\LexofficeApiClient\Endpoint;

use Four\LexofficeApiClient\DTO\PagedResponse;
use Four\LexofficeApiClient\Exception\LexofficeException;

interface VoucherlistEndpointInterface
{
    /**
     * Listet Vouchers gefiltert und paginiert auf.
     *
     * @param array<string, string> $filters  Erlaubt: voucherType, voucherStatus, contactId,
     *                                        voucherDateFrom (yyyy-MM-dd), voucherDateTo (yyyy-MM-dd), archived
     * @throws LexofficeException
     */
    public function list(array $filters = [], int $page = 0): PagedResponse;
}
