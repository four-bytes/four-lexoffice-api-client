<?php

declare(strict_types=1);

namespace Four\LexwareApiClient\Endpoint;

use Four\LexwareApiClient\DTO\EventSubscription;
use Four\LexwareApiClient\Exception\LexwareException;
use Four\LexwareApiClient\LexwareClient;

class EventSubscriptionEndpoint implements EventSubscriptionEndpointInterface
{
    public function __construct(
        private readonly LexwareClient $client,
    ) {
    }

    /**
     * Creates a new event subscription.
     *
     * @return array<string, mixed>  {id}
     * @throws LexwareException
     */
    public function create(EventSubscription $subscription): array
    {
        /** @var array<string, mixed> $result */
        $result = $this->client->sendRequest('event-subscriptions', $subscription->toApiArray(), 'POST');
        return $result;
    }

    /**
     * Returns an event subscription by ID.
     *
     * @return array<string, mixed>
     * @throws LexwareException
     */
    public function get(string $id): array
    {
        /** @var array<string, mixed> $result */
        $result = $this->client->sendRequest('event-subscriptions/' . $id);
        return $result;
    }

    /**
     * Returns all event subscriptions.
     *
     * @return array<mixed>
     * @throws LexwareException
     */
    public function list(): array
    {
        /** @var array<mixed> $result */
        $result = $this->client->sendRequest('event-subscriptions');
        return $result;
    }

    /**
     * Deletes an event subscription (204 No Content).
     *
     * @throws LexwareException
     */
    public function delete(string $id): void
    {
        $this->client->sendRequest('event-subscriptions/' . $id, null, 'DELETE');
    }
}
