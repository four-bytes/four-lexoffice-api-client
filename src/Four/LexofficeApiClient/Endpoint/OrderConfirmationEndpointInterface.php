<?php

declare(strict_types=1);

namespace Four\LexofficeApiClient\Endpoint;

use Four\LexofficeApiClient\DTO\OrderConfirmation;
use Four\LexofficeApiClient\Exception\LexofficeException;

interface OrderConfirmationEndpointInterface
{
    /**
     * Erstellt eine neue Auftragsbestätigung.
     *
     * @return array<string, mixed>  {id, resourceUri, version}
     * @throws LexofficeException
     */
    public function create(OrderConfirmation $orderConfirmation, bool $finalize = false): array;

    /**
     * Leitet einen bestehenden Voucher in eine neue Auftragsbestätigung über (Pursue).
     *
     * @return array<string, mixed>  {id, resourceUri, version}
     * @throws LexofficeException
     */
    public function pursue(string $precedingId, OrderConfirmation $orderConfirmation, bool $finalize = false): array;

    /**
     * Gibt eine Auftragsbestätigung anhand der ID zurück.
     *
     * @return array<string, mixed>
     * @throws LexofficeException
     */
    public function get(string $id): array;

    /**
     * Lädt die PDF-Datei einer Auftragsbestätigung herunter.
     *
     * @throws LexofficeException
     */
    public function downloadPdf(string $id): string;
}
