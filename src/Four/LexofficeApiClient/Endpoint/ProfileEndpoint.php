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
     * Gibt das Profil des aktuellen API-Nutzers zurück.
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
