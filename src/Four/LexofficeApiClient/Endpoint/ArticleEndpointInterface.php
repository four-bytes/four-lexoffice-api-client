<?php

declare(strict_types=1);

namespace Four\LexofficeApiClient\Endpoint;

use Four\LexofficeApiClient\DTO\Article;
use Four\LexofficeApiClient\DTO\PagedResponse;
use Four\LexofficeApiClient\Exception\LexofficeException;

interface ArticleEndpointInterface
{
    /**
     * Creates a new article.
     *
     * @return array<string, mixed>  {id, resourceUri, version}
     * @throws LexofficeException
     */
    public function create(Article $article): array;

    /**
     * Returns an article by ID.
     *
     * @return array<string, mixed>
     * @throws LexofficeException
     */
    public function get(string $id): array;

    /**
     * Updates an article (optimistic locking: pass version).
     *
     * @return array<string, mixed>  {id, resourceUri, version}
     * @throws LexofficeException
     */
    public function update(string $id, Article $article): array;

    /**
     * Deletes an article (204 No Content).
     *
     * @throws LexofficeException
     */
    public function delete(string $id): void;

    /**
     * Lists articles, paginated.
     *
     * @param array<string, string> $filters  Erlaubt: articleNumber, gtin, type
     * @throws LexofficeException
     */
    public function list(int $page = 0, array $filters = []): PagedResponse;
}
