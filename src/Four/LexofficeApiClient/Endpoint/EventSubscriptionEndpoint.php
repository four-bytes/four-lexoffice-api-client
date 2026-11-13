<?php

declare(strict_types=1);

namespace Four\LexofficeApiClient\Endpoint;

use Four\LexofficeApiClient\DTO\EventSubscription;
use Four\LexofficeApiClient\Exception\LexofficeException;
use Four\LexofficeApiClient\LexofficeClient;

class EventSubscriptionEndpoint implements EventSubscriptionEndpointInterface
{
    public function __construct(
        private readonly LexofficeClient $client,
    ) {
    }

    /**
     * Creates a new event subscription.
     *
     * @return array<string, mixed>  {id}
     * @throws LexofficeException
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
     * @throws LexofficeException
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
     * @throws LexofficeException
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
     * @throws LexofficeException
     */
    public function delete(string $id): void
    {
        $this->client->sendRequest('event-subscriptions/' . $id, null, 'DELETE');
    }
}
