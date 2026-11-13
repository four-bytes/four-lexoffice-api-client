<?php

declare(strict_types=1);

namespace Four\LexofficeApiClient\Endpoint;

use Four\LexofficeApiClient\Exception\LexofficeException;

interface ProfileEndpointInterface
{
    /**
     * Returns the profile of the current API user.
     *
     * @return array<string, mixed>
     * @throws LexofficeException
     */
    public function get(): array;
}
