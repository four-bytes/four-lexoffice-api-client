<?php

declare(strict_types=1);

namespace Four\LexofficeApiClient\Endpoint;

use Four\LexofficeApiClient\Exception\LexofficeException;
use Four\LexofficeApiClient\LexofficeClient;

class RecurringTemplateEndpoint implements RecurringTemplateEndpointInterface
{
    public function __construct(
        private readonly LexofficeClient $client,
    ) {
    }

    /**
     * Gibt alle Vorlagen für wiederkehrende Rechnungen paginiert zurück.
     *
     * @return array<mixed>
     * @throws LexofficeException
     */
    public function list(int $page = 0): array
    {
        /** @var array<mixed> $result */
        $result = $this->client->sendRequest('recurring-templates?page=' . $page);
        return $result;
    }
}
