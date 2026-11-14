<?php

declare(strict_types=1);

namespace Four\LexwareApiClient\Endpoint;

use Four\LexwareApiClient\DTO\EventSubscription;
use Four\LexwareApiClient\Exception\LexwareException;

interface EventSubscriptionEndpointInterface
{
    /**
     * Creates a new event subscription.
     *
     * @return array<string, mixed>  {id}
     * @throws LexwareException
     */
    public function create(EventSubscription $subscription): array;

    /**
     * Returns an event subscription by ID.
     *
     * @return array<string, mixed>
     * @throws LexwareException
     */
    public function get(string $id): array;

    /**
     * Returns all event subscriptions.
     *
     * @return array<mixed>
     * @throws LexwareException
     */
    public function list(): array;

    /**
     * Deletes an event subscription (204 No Content).
     *
     * @throws LexwareException
     */
    public function delete(string $id): void;
}
