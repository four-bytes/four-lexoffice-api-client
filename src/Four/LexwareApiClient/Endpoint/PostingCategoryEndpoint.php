<?php

declare(strict_types=1);

namespace Four\LexwareApiClient\Endpoint;

use Four\LexwareApiClient\Exception\LexwareException;
use Four\LexwareApiClient\LexwareClient;

class PostingCategoryEndpoint implements PostingCategoryEndpointInterface
{
    public function __construct(
        private readonly LexwareClient $client,
    ) {
    }

    /**
     * Returns all posting categories.
     *
     * @return array<mixed>
     * @throws LexwareException
     */
    public function list(): array
    {
        /** @var array<mixed> $result */
        $result = $this->client->sendRequest('posting-categories');
        return $result;
    }
}
