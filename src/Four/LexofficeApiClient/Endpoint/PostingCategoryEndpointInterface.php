<?php

declare(strict_types=1);

namespace Four\LexofficeApiClient\Endpoint;

use Four\LexofficeApiClient\Exception\LexofficeException;

interface PostingCategoryEndpointInterface
{
    /**
     * Returns all posting categories.
     *
     * @return array<mixed>
     * @throws LexofficeException
     */
    public function list(): array;
}
