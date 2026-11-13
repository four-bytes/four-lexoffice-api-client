<?php

declare(strict_types=1);

namespace Four\LexofficeApiClient\Endpoint;

use Four\LexofficeApiClient\DTO\EventSubscription;
use Four\LexofficeApiClient\Exception\LexofficeException;

interface EventSubscriptionEndpointInterface
{
    /**
     * Erstellt ein neues Event-Subscription.
     *
     * @return array<string, mixed>  {id}
     * @throws LexofficeException
     */
    public function create(EventSubscription $subscription): array;

    /**
     * Gibt eine Event-Subscription anhand der ID zurück.
     *
     * @return array<string, mixed>
     * @throws LexofficeException
     */
    public function get(string $id): array;

    /**
     * Gibt alle Event-Subscriptions zurück.
     *
     * @return array<mixed>
     * @throws LexofficeException
     */
    public function list(): array;

    /**
     * Löscht eine Event-Subscription (204 No Content).
     *
     * @throws LexofficeException
     */
    public function delete(string $id): void;
}
