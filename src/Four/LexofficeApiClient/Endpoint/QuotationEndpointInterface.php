<?php

declare(strict_types=1);

namespace Four\LexofficeApiClient\Endpoint;

use Four\LexofficeApiClient\DTO\Quotation;
use Four\LexofficeApiClient\Exception\LexofficeException;

interface QuotationEndpointInterface
{
    /**
     * Erstellt ein neues Angebot.
     *
     * @return array<string, mixed>  {id, resourceUri, version}
     * @throws LexofficeException
     */
    public function create(Quotation $quotation, bool $finalize = false): array;

    /**
     * Leitet einen bestehenden Voucher in ein neues Angebot über (Pursue).
     *
     * @return array<string, mixed>  {id, resourceUri, version}
     * @throws LexofficeException
     */
    public function pursue(string $precedingId, Quotation $quotation, bool $finalize = false): array;

    /**
     * Gibt ein Angebot anhand der ID zurück.
     *
     * @return array<string, mixed>
     * @throws LexofficeException
     */
    public function get(string $id): array;

    /**
     * Lädt die PDF-Datei eines Angebots herunter.
     *
     * @throws LexofficeException
     */
    public function downloadPdf(string $id): string;
}
