<?php

declare(strict_types=1);

namespace Four\LexofficeApiClient\Endpoint;

use Four\LexofficeApiClient\Exception\LexofficeException;
use Four\LexofficeApiClient\LexofficeClient;

class PostingCategoryEndpoint implements PostingCategoryEndpointInterface
{
    public function __construct(
        private readonly LexofficeClient $client,
    ) {
    }

    /**
     * Returns all posting categories.
     *
     * @return array<mixed>
     * @throws LexofficeException
     */
    public function list(): array
    {
        /** @var array<mixed> $result */
        $result = $this->client->sendRequest('posting-categories');
        return $result;
    }
}
