<?php

declare(strict_types=1);

namespace Four\LexofficeApiClient\Endpoint;

use Four\LexofficeApiClient\DTO\CreditNote;
use Four\LexofficeApiClient\Exception\LexofficeException;

interface CreditNoteEndpointInterface
{
    /**
     * Erstellt eine neue Gutschrift.
     *
     * @return array<string, mixed>  {id, resourceUri, version}
     * @throws LexofficeException
     */
    public function create(CreditNote $creditNote, bool $finalize = false): array;

    /**
     * Leitet einen bestehenden Voucher in eine neue Gutschrift über (Pursue).
     *
     * @return array<string, mixed>  {id, resourceUri, version}
     * @throws LexofficeException
     */
    public function pursue(string $precedingId, CreditNote $creditNote, bool $finalize = false): array;

    /**
     * Gibt eine Gutschrift anhand der ID zurück.
     *
     * @return array<string, mixed>
     * @throws LexofficeException
     */
    public function get(string $id): array;

    /**
     * Lädt die PDF-Datei einer Gutschrift herunter.
     *
     * @throws LexofficeException
     */
    public function downloadPdf(string $id): string;
}
