<?php

declare(strict_types=1);

namespace Four\LexwareApiClient\Endpoint;

use Four\LexwareApiClient\Exception\LexwareException;

interface ProfileEndpointInterface
{
    /**
     * Returns the profile of the current API user.
     *
     * @return array<string, mixed>
     * @throws LexwareException
     */
    public function get(): array;
}
