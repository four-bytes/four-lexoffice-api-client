<?php

declare(strict_types=1);

namespace Four\LexwareApiClient\Endpoint;

use Four\LexwareApiClient\Exception\LexwareException;
use Four\LexwareApiClient\LexwareClient;

class RecurringTemplateEndpoint implements RecurringTemplateEndpointInterface
{
    public function __construct(
        private readonly LexwareClient $client,
    ) {
    }

    /**
     * Returns all recurring invoice templates, paginated.
     *
     * @param int         $size Page size, 1–250 (API default 25).
     * @param string|null $sort createdDate, updatedDate, lastExecutionDate or nextExecutionDate, optionally ",ASC"/",DESC".
     * @return array<mixed>
     * @throws LexwareException
     */
    public function list(int $page = 0, int $size = 25, ?string $sort = null): array
    {
        $query = ['page' => $page, 'size' => $size];
        if ($sort !== null) {
            $query['sort'] = $sort;
        }

        /** @var array<mixed> $result */
        $result = $this->client->sendRequest('recurring-templates?' . http_build_query($query));
        return $result;
    }

    /**
     * Returns a recurring invoice template by ID.
     *
     * @return array<string, mixed>
     * @throws LexwareException
     */
    public function get(string $id): array
    {
        /** @var array<string, mixed> $result */
        $result = $this->client->sendRequest('recurring-templates/' . rawurlencode($id));
        return $result;
    }
}
