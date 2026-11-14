<?php

declare(strict_types=1);

namespace Four\LexwareApiClient\Endpoint;

use Four\LexwareApiClient\Exception\LexwareException;

interface PrintLayoutEndpointInterface
{
    /**
     * Returns all available print layouts.
     *
     * @return array<mixed>
     * @throws LexwareException
     */
    public function list(): array;
}
