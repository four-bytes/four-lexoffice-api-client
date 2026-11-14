<?php

declare(strict_types=1);

namespace Four\LexwareApiClient\Endpoint;

use Four\LexwareApiClient\DTO\PagedResponse;
use Four\LexwareApiClient\Exception\LexwareException;
use Four\LexwareApiClient\LexwareClient;

class VoucherlistEndpoint implements VoucherlistEndpointInterface
{
    public function __construct(
        private readonly LexwareClient $client,
    ) {
    }

    /**
     * Lists vouchers filtered and paginated.
     *
     * @param array<string, string> $filters  Erlaubt: voucherType, voucherStatus, contactId,
     *                                        voucherDateFrom (yyyy-MM-dd), voucherDateTo (yyyy-MM-dd), archived
     * @throws LexwareException
     */
    public function list(array $filters = [], int $page = 0): PagedResponse
    {
        $params = array_merge($filters, ['page' => (string) $page]);
        $query  = http_build_query($params);

        $data = $this->client->sendRequest('voucherlist?' . $query);
        return PagedResponse::fromArray($data);
    }
}
