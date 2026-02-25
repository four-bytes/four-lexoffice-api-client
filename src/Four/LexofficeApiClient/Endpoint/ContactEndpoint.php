<?php

declare(strict_types=1);

namespace Four\LexofficeApiClient\Endpoint;

use Four\LexofficeApiClient\DTO\Contact;
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
}
