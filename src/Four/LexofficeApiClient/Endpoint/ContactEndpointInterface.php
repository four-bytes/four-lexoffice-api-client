<?php

declare(strict_types=1);

namespace Four\LexofficeApiClient\Endpoint;

use Four\LexofficeApiClient\DTO\Contact;
use Four\LexofficeApiClient\DTO\PagedResponse;
use Four\LexofficeApiClient\Exception\LexofficeException;

interface ContactEndpointInterface
{
    /**
     * Returns a contact by ID.
     *
     * @return array<string, mixed>
     * @throws LexofficeException
     */
    public function get(string $contactId): array;

    /**
     * Creates a new contact and returns its ID.
     *
     * @throws LexofficeException
     */
    public function create(Contact $contact): string;

    /**
     * Updates an existing contact and returns its ID.
     *
     * @throws LexofficeException
     */
    public function update(string $contactId, Contact $contact): string;

    /**
     * Creates or updates a contact (upsert).
     * If existingId is provided, the contact is updated, otherwise a new one is created.
     *
     * @throws LexofficeException
     */
    public function upsert(Contact $contact, ?string $existingId = null): string;

    /**
     * Finds a contact by email address.
     *
     * @return array<string, mixed>|null
     * @throws LexofficeException
     */
    public function findByEmail(string $email): ?array;

    /**
     * Finds contacts by name (paginated).
     *
     * @throws LexofficeException
     */
    public function findByName(string $name): PagedResponse;

    /**
     * Lists contacts, paginated.
     *
     * @param array<string, string> $filters
     * @throws LexofficeException
     */
    public function list(int $page = 0, array $filters = []): PagedResponse;
}
