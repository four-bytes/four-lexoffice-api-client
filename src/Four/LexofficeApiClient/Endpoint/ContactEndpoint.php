<?php

declare(strict_types=1);

namespace Four\LexofficeApiClient\Endpoint;

use Four\LexofficeApiClient\DTO\Contact;
use Four\LexofficeApiClient\DTO\PagedResponse;
use Four\LexofficeApiClient\Exception\LexofficeException;
use Four\LexofficeApiClient\LexofficeClient;

class ContactEndpoint
{
    public function __construct(
        private readonly LexofficeClient $client,
    ) {
    }

    /**
     * Gibt einen Kontakt anhand der ID zurück.
     *
     * @return array<string, mixed>
     * @throws LexofficeException
     */
    public function get(string $contactId): array
    {
        /** @var array<string, mixed> $result */
        $result = $this->client->sendRequest('contacts/' . $contactId);
        return $result;
    }

    /**
     * Erstellt einen neuen Kontakt und gibt die ID zurück.
     *
     * @throws LexofficeException
     */
    public function create(Contact $contact): string
    {
        $response = $this->client->sendRequest('contacts', $contact->toApiArray(), 'POST');

        if (!isset($response['id']) || !is_string($response['id'])) {
            throw new LexofficeException(
                message: 'Ungültige API-Antwort: Kein "id" Feld in der Antwort',
                httpStatusCode: 200,
                responseBody: (string) json_encode($response),
            );
        }

        return $response['id'];
    }

    /**
     * Aktualisiert einen bestehenden Kontakt und gibt die ID zurück.
     *
     * @throws LexofficeException
     */
    public function update(string $contactId, Contact $contact): string
    {
        $payload = $contact->toApiArray();
        $this->client->sendRequest('contacts/' . $contactId, $payload, 'PUT');
        return $contactId;
    }

    /**
     * Erstellt oder aktualisiert einen Kontakt (upsert).
     * Wenn existingId übergeben wird, wird aktualisiert, sonst neu erstellt.
     *
     * @throws LexofficeException
     */
    public function upsert(Contact $contact, ?string $existingId = null): string
    {
        if ($existingId !== null && $existingId !== '') {
            return $this->update($existingId, $contact);
        }

        return $this->create($contact);
    }

    /**
     * Sucht einen Kontakt anhand der E-Mail-Adresse.
     *
     * @return array<string, mixed>|null
     * @throws LexofficeException
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
     * Sucht Kontakte anhand des Namens (paginiert).
     *
     * @throws LexofficeException
     */
    public function findByName(string $name): PagedResponse
    {
        $query = http_build_query(['name' => $name]);
        $data  = $this->client->sendRequest('contacts?' . $query);
        return PagedResponse::fromArray($data);
    }

    /**
     * Listet Kontakte paginiert auf.
     *
     * @param array<string, string> $filters
     * @throws LexofficeException
     */
    public function list(int $page = 0, array $filters = []): PagedResponse
    {
        $params = array_merge(['page' => (string) $page], $filters);
        $query  = http_build_query($params);

        $data = $this->client->sendRequest('contacts?' . $query);
        return PagedResponse::fromArray($data);
    }
}
