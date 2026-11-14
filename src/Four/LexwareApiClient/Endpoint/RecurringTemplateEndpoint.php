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
     * @return array<mixed>
     * @throws LexwareException
     */
    public function list(int $page = 0): array
    {
        /** @var array<mixed> $result */
        $result = $this->client->sendRequest('recurring-templates?page=' . $page);
        return $result;
    }
}
