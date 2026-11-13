<?php

declare(strict_types=1);

namespace Four\LexofficeApiClient\Endpoint;

use Four\LexofficeApiClient\DTO\Article;
use Four\LexofficeApiClient\DTO\PagedResponse;
use Four\LexofficeApiClient\Exception\LexofficeException;

interface ArticleEndpointInterface
{
    /**
     * Legt einen neuen Artikel an.
     *
     * @return array<string, mixed>  {id, resourceUri, version}
     * @throws LexofficeException
     */
    public function create(Article $article): array;

    /**
     * Gibt einen Artikel anhand der ID zurück.
     *
     * @return array<string, mixed>
     * @throws LexofficeException
     */
    public function get(string $id): array;

    /**
     * Aktualisiert einen Artikel (Optimistic Locking: version mitgeben).
     *
     * @return array<string, mixed>  {id, resourceUri, version}
     * @throws LexofficeException
     */
    public function update(string $id, Article $article): array;

    /**
     * Löscht einen Artikel (204 No Content).
     *
     * @throws LexofficeException
     */
    public function delete(string $id): void;

    /**
     * Listet Artikel paginiert auf.
     *
     * @param array<string, string> $filters  Erlaubt: articleNumber, gtin, type
     * @throws LexofficeException
     */
    public function list(int $page = 0, array $filters = []): PagedResponse;
}
