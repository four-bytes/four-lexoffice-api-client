<?php

declare(strict_types=1);

namespace Four\LexwareApiClient\Endpoint;

use Four\LexwareApiClient\DTO\Contact;
use Four\LexwareApiClient\DTO\PagedResponse;
use Four\LexwareApiClient\Exception\LexwareException;

interface ContactEndpointInterface
{
    /**
     * Returns a contact by ID.
     *
     * @return array<string, mixed>
     * @throws LexwareException
     */
    public function get(string $contactId): array;

    /**
     * Creates a new contact and returns its ID.
     *
     * @throws LexwareException
     */
    public function create(Contact $contact): string;

    /**
     * Updates an existing contact and returns its ID.
     *
     * @throws LexwareException
     */
    public function update(string $contactId, Contact $contact): string;

    /**
     * Creates or updates a contact (upsert).
     * If existingId is provided, the contact is updated, otherwise a new one is created.
     *
     * @throws LexwareException
     */
    public function upsert(Contact $contact, ?string $existingId = null): string;

    /**
     * Finds a contact by email address.
     *
     * @return array<string, mixed>|null
     * @throws LexwareException
     */
    public function findByEmail(string $email): ?array;

    /**
     * Finds contacts by name (paginated).
     *
     * @throws LexwareException
     */
    public function findByName(string $name): PagedResponse;

    /**
     * Lists contacts, paginated.
     *
     * @param array<string, string> $filters
     * @throws LexwareException
     */
    public function list(int $page = 0, array $filters = []): PagedResponse;
}
