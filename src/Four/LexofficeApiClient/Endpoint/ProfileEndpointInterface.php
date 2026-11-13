<?php

declare(strict_types=1);

namespace Four\LexofficeApiClient\Endpoint;

use Four\LexofficeApiClient\Exception\LexofficeException;

interface ProfileEndpointInterface
{
    /**
     * Gibt das Profil des aktuellen API-Nutzers zurück.
     *
     * @return array<string, mixed>
     * @throws LexofficeException
     */
    public function get(): array;
}
