<?php

declare(strict_types=1);

namespace Four\LexwareApiClient\Endpoint;

use Four\LexwareApiClient\Exception\LexwareException;

interface CountryEndpointInterface
{
    /**
     * Returns all available countries.
     *
     * @return array<mixed>
     * @throws LexwareException
     */
    public function list(): array;
}
