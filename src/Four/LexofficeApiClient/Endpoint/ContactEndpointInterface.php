<?php

declare(strict_types=1);

namespace Four\LexofficeApiClient\Endpoint;

use Four\LexofficeApiClient\DTO\Contact;
use Four\LexofficeApiClient\DTO\PagedResponse;
use Four\LexofficeApiClient\Exception\LexofficeException;

interface ContactEndpointInterface
{
    /**
     * Gibt einen Kontakt anhand der ID zurück.
     *
     * @return array<string, mixed>
     * @throws LexofficeException
     */
    public function get(string $contactId): array;

    /**
     * Erstellt einen neuen Kontakt und gibt die ID zurück.
     *
     * @throws LexofficeException
     */
    public function create(Contact $contact): string;

    /**
     * Aktualisiert einen bestehenden Kontakt und gibt die ID zurück.
     *
     * @throws LexofficeException
     */
    public function update(string $contactId, Contact $contact): string;

    /**
     * Erstellt oder aktualisiert einen Kontakt (upsert).
     * Wenn existingId übergeben wird, wird aktualisiert, sonst neu erstellt.
     *
     * @throws LexofficeException
     */
    public function upsert(Contact $contact, ?string $existingId = null): string;

    /**
     * Sucht einen Kontakt anhand der E-Mail-Adresse.
     *
     * @return array<string, mixed>|null
     * @throws LexofficeException
     */
    public function findByEmail(string $email): ?array;

    /**
     * Sucht Kontakte anhand des Namens (paginiert).
     *
     * @throws LexofficeException
     */
    public function findByName(string $name): PagedResponse;

    /**
     * Listet Kontakte paginiert auf.
     *
     * @param array<string, string> $filters
     * @throws LexofficeException
     */
    public function list(int $page = 0, array $filters = []): PagedResponse;
}
