<?php

declare(strict_types=1);

namespace Four\LexofficeApiClient\Endpoint;

use Four\LexofficeApiClient\DTO\PagedResponse;
use Four\LexofficeApiClient\Exception\LexofficeException;
use Four\LexofficeApiClient\LexofficeClient;

class VoucherlistEndpoint implements VoucherlistEndpointInterface
{
    public function __construct(
        private readonly LexofficeClient $client,
    ) {
    }

    /**
     * Listet Vouchers gefiltert und paginiert auf.
     *
     * @param array<string, string> $filters  Erlaubt: voucherType, voucherStatus, contactId,
     *                                        voucherDateFrom (yyyy-MM-dd), voucherDateTo (yyyy-MM-dd), archived
     * @throws LexofficeException
     */
    public function list(array $filters = [], int $page = 0): PagedResponse
    {
        $params = array_merge($filters, ['page' => (string) $page]);
        $query  = http_build_query($params);

        $data = $this->client->sendRequest('voucherlist?' . $query);
        return PagedResponse::fromArray($data);
    }
}
