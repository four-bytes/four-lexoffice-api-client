<?php

declare(strict_types=1);

namespace Four\LexwareApiClient\Endpoint;

use Four\LexwareApiClient\DTO\Article;
use Four\LexwareApiClient\DTO\PagedResponse;
use Four\LexwareApiClient\Exception\LexwareException;

interface ArticleEndpointInterface
{
    /**
     * Creates a new article.
     *
     * @return array<string, mixed>  {id, resourceUri, version}
     * @throws LexwareException
     */
    public function create(Article $article): array;

    /**
     * Returns an article by ID.
     *
     * @return array<string, mixed>
     * @throws LexwareException
     */
    public function get(string $id): array;

    /**
     * Updates an article (optimistic locking: pass version).
     *
     * @return array<string, mixed>  {id, resourceUri, version}
     * @throws LexwareException
     */
    public function update(string $id, Article $article): array;

    /**
     * Deletes an article (204 No Content).
     *
     * @throws LexwareException
     */
    public function delete(string $id): void;

    /**
     * Lists articles, paginated.
     *
     * @param array<string, string> $filters  Erlaubt: articleNumber, gtin, type
     * @throws LexwareException
     */
    public function list(int $page = 0, array $filters = []): PagedResponse;
}
