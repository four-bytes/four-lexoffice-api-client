<?php

declare(strict_types=1);

namespace Four\LexwareApiClient\Endpoint;

use Four\LexwareApiClient\DTO\Contact;
use Four\LexwareApiClient\DTO\PagedResponse;
use Four\LexwareApiClient\Exception\LexwareException;
use Four\LexwareApiClient\LexwareClient;

class ContactEndpoint implements ContactEndpointInterface
{
    public function __construct(
        private readonly LexwareClient $client,
    ) {
    }

    /**
     * Returns a contact by ID.
     *
     * @return array<string, mixed>
     * @throws LexwareException
     */
    public function get(string $contactId): array
    {
        /** @var array<string, mixed> $result */
        $result = $this->client->sendRequest('contacts/' . $contactId);
        return $result;
    }

    /**
     * Creates a new contact and returns its ID.
     *
     * @throws LexwareException
     */
    public function create(Contact $contact): string
    {
        $response = $this->client->sendRequest('contacts', $contact->toApiArray(), 'POST');

        if (!isset($response['id']) || !is_string($response['id'])) {
            throw new LexwareException(
                message: 'Ungültige API-Antwort: Kein "id" Feld in der Antwort',
                httpStatusCode: 200,
                responseBody: (string) json_encode($response),
            );
        }

        return $response['id'];
    }

    /**
     * Updates an existing contact and returns its ID.
     *
     * @throws LexwareException
     */
    public function update(string $contactId, Contact $contact): string
    {
        $payload = $contact->toApiArray();
        $this->client->sendRequest('contacts/' . $contactId, $payload, 'PUT');
        return $contactId;
    }

    /**
     * Creates or updates a contact (upsert).
     * If existingId is provided, the contact is updated, otherwise a new one is created.
     *
     * @throws LexwareException
     */
    public function upsert(Contact $contact, ?string $existingId = null): string
    {
        if ($existingId !== null && $existingId !== '') {
            return $this->update($existingId, $contact);
        }

        return $this->create($contact);
    }

    /**
     * Finds a contact by email address.
     *
     * @return array<string, mixed>|null
     * @throws LexwareException
     */
    public function findByEmail(string $email): ?array
    {
        $query  = http_build_query(['email' => $email]);
        $result = $this->client->sendRequest('contacts?' . $query);

        if (isset($result['content']) && is_array($result['content']) && count($result['content']) > 0) {
            /** @var array<string, mixed> $first */
            $first = $result['content'][0];
            return $first;
        }

        return null;
    }

    /**
     * Finds contacts by name (paginated).
     *
     * @throws LexwareException
     */
    public function findByName(string $name): PagedResponse
    {
        $query = http_build_query(['name' => $name]);
        $data  = $this->client->sendRequest('contacts?' . $query);
        return PagedResponse::fromArray($data);
    }

    /**
     * Lists contacts, paginated.
     *
     * @param array<string, string> $filters
     * @throws LexwareException
     */
    public function list(int $page = 0, array $filters = []): PagedResponse
    {
        $params = array_merge(['page' => (string) $page], $filters);
        $query  = http_build_query($params);

        $data = $this->client->sendRequest('contacts?' . $query);
        return PagedResponse::fromArray($data);
    }
}
