<?php

declare(strict_types=1);

namespace Four\LexwareApiClient\Endpoint;

use Four\LexwareApiClient\Exception\LexwareException;

interface RecurringTemplateEndpointInterface
{
    /**
     * Returns all recurring invoice templates, paginated.
     *
     * @return array<mixed>
     * @throws LexwareException
     */
    public function list(int $page = 0): array;
}
