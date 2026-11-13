<?php

declare(strict_types=1);

namespace Four\LexofficeApiClient\Endpoint;

use Four\LexofficeApiClient\Exception\LexofficeException;

interface PaymentConditionEndpointInterface
{
    /**
     * Returns all payment conditions.
     *
     * @return array<mixed>
     * @throws LexofficeException
     */
    public function list(): array;
}
