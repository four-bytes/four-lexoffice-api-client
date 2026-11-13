<?php

declare(strict_types=1);

namespace Four\LexofficeApiClient\Endpoint;

use Four\LexofficeApiClient\Exception\LexofficeException;

interface PrintLayoutEndpointInterface
{
    /**
     * Returns all available print layouts.
     *
     * @return array<mixed>
     * @throws LexofficeException
     */
    public function list(): array;
}
