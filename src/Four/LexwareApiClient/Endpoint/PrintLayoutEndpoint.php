<?php

declare(strict_types=1);

namespace Four\LexwareApiClient\Endpoint;

use Four\LexwareApiClient\Exception\LexwareException;
use Four\LexwareApiClient\LexwareClient;

class PrintLayoutEndpoint implements PrintLayoutEndpointInterface
{
    public function __construct(
        private readonly LexwareClient $client,
    ) {
    }

    /**
     * Returns all available print layouts.
     *
     * @return array<mixed>
     * @throws LexwareException
     */
    public function list(): array
    {
        /** @var array<mixed> $result */
        $result = $this->client->sendRequest('print-layouts');
        return $result;
    }
}
