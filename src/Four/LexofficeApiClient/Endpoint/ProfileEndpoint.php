<?php

declare(strict_types=1);

namespace Four\LexofficeApiClient\Endpoint;

use Four\LexofficeApiClient\Exception\LexofficeException;
use Four\LexofficeApiClient\LexofficeClient;

class ProfileEndpoint implements ProfileEndpointInterface
{
    public function __construct(
        private readonly LexofficeClient $client,
    ) {
    }

    /**
     * Returns the profile of the current API user.
     *
     * @return array<string, mixed>
     * @throws LexofficeException
     */
    public function get(): array
    {
        /** @var array<string, mixed> $result */
        $result = $this->client->sendRequest('profile');
        return $result;
    }
}
