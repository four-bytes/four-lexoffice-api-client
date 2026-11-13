<?php

declare(strict_types=1);

namespace Four\LexofficeApiClient\Endpoint;

use Four\LexofficeApiClient\DTO\Dunning;
use Four\LexofficeApiClient\Exception\LexofficeException;

interface DunningEndpointInterface
{
    /**
     * Erstellt eine neue Mahnung (draft oder direkt finalisiert).
     *
     * @return array<string, mixed>  {id, resourceUri, version}
     * @throws LexofficeException
     */
    public function create(Dunning $dunning, bool $finalize = false): array;

    /**
     * Leitet einen bestehenden Voucher in eine neue Mahnung über (Pursue).
     *
     * @return array<string, mixed>  {id, resourceUri, version}
     * @throws LexofficeException
     */
    public function pursue(string $precedingId, Dunning $dunning, bool $finalize = false): array;

    /**
     * Gibt eine Mahnung anhand der ID zurück.
     *
     * @return array<string, mixed>
     * @throws LexofficeException
     */
    public function get(string $id): array;

    /**
     * Lädt die PDF-Datei einer Mahnung herunter und gibt die rohen Bytes zurück.
     *
     * @throws LexofficeException
     */
    public function downloadPdf(string $id): string;
}
