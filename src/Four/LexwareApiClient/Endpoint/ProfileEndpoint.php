<?php

declare(strict_types=1);

namespace Four\LexwareApiClient\Endpoint;

use Four\LexwareApiClient\Exception\LexwareException;
use Four\LexwareApiClient\LexwareClient;

class ProfileEndpoint implements ProfileEndpointInterface
{
    public function __construct(
        private readonly LexwareClient $client,
    ) {
    }

    /**
     * Returns the profile of the current API user.
     *
     * @return array<string, mixed>
     * @throws LexwareException
     */
    public function get(): array
    {
        /** @var array<string, mixed> $result */
        $result = $this->client->sendRequest('profile');
        return $result;
    }
}
