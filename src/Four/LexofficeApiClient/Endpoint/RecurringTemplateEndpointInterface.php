<?php

declare(strict_types=1);

namespace Four\LexofficeApiClient\Endpoint;

use Four\LexofficeApiClient\Exception\LexofficeException;

interface RecurringTemplateEndpointInterface
{
    /**
     * Returns all recurring invoice templates, paginated.
     *
     * @return array<mixed>
     * @throws LexofficeException
     */
    public function list(int $page = 0): array;
}
