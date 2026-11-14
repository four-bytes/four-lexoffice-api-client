<?php

declare(strict_types=1);

namespace Four\LexwareApiClient\Endpoint;

use Four\LexwareApiClient\Exception\LexwareException;

interface PaymentConditionEndpointInterface
{
    /**
     * Returns all payment conditions.
     *
     * @return array<mixed>
     * @throws LexwareException
     */
    public function list(): array;
}
