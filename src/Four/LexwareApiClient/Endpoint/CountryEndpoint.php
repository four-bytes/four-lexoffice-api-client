<?php

declare(strict_types=1);

namespace Four\LexwareApiClient\Endpoint;

use Four\LexwareApiClient\Exception\LexwareException;
use Four\LexwareApiClient\LexwareClient;

class CountryEndpoint implements CountryEndpointInterface
{
    public function __construct(
        private readonly LexwareClient $client,
    ) {
    }

    /**
     * Returns all available countries.
     *
     * @return array<mixed>
     * @throws LexwareException
     */
    public function list(): array
    {
        /** @var array<mixed> $result */
        $result = $this->client->sendRequest('countries');
        return $result;
    }
}
