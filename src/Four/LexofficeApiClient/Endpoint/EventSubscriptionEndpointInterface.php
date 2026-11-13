<?php

declare(strict_types=1);

namespace Four\LexofficeApiClient\Endpoint;

use Four\LexofficeApiClient\DTO\EventSubscription;
use Four\LexofficeApiClient\Exception\LexofficeException;

interface EventSubscriptionEndpointInterface
{
    /**
     * Creates a new event subscription.
     *
     * @return array<string, mixed>  {id}
     * @throws LexofficeException
     */
    public function create(EventSubscription $subscription): array;

    /**
     * Returns an event subscription by ID.
     *
     * @return array<string, mixed>
     * @throws LexofficeException
     */
    public function get(string $id): array;

    /**
     * Returns all event subscriptions.
     *
     * @return array<mixed>
     * @throws LexofficeException
     */
    public function list(): array;

    /**
     * Deletes an event subscription (204 No Content).
     *
     * @throws LexofficeException
     */
    public function delete(string $id): void;
}
