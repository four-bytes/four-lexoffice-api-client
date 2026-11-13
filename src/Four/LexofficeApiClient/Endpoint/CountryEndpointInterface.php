<?php

declare(strict_types=1);

namespace Four\LexofficeApiClient\Endpoint;

use Four\LexofficeApiClient\Exception\LexofficeException;

interface CountryEndpointInterface
{
    /**
     * Gibt alle verfügbaren Länder zurück.
     *
     * @return array<mixed>
     * @throws LexofficeException
     */
    public function list(): array;
}
