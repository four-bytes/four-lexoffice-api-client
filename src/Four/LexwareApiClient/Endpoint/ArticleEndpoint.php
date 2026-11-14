<?php

declare(strict_types=1);

namespace Four\LexwareApiClient\Endpoint;

use Four\LexwareApiClient\DTO\Article;
use Four\LexwareApiClient\DTO\PagedResponse;
use Four\LexwareApiClient\Exception\LexwareException;
use Four\LexwareApiClient\LexwareClient;

class ArticleEndpoint implements ArticleEndpointInterface
{
    public function __construct(
        private readonly LexwareClient $client,
    ) {
    }

    /**
     * Creates a new article.
     *
     * @return array<string, mixed>  {id, resourceUri, version}
     * @throws LexwareException
     */
    public function create(Article $article): array
    {
        /** @var array<string, mixed> $result */
        $result = $this->client->sendRequest('articles', $article->toApiArray(), 'POST');
        return $result;
    }

    /**
     * Returns an article by ID.
     *
     * @return array<string, mixed>
     * @throws LexwareException
     */
    public function get(string $id): array
    {
        /** @var array<string, mixed> $result */
        $result = $this->client->sendRequest('articles/' . $id);
        return $result;
    }

    /**
     * Updates an article (optimistic locking: pass version).
     *
     * @return array<string, mixed>  {id, resourceUri, version}
     * @throws LexwareException
     */
    public function update(string $id, Article $article): array
    {
        /** @var array<string, mixed> $result */
        $result = $this->client->sendRequest('articles/' . $id, $article->toApiArray(), 'PUT');
        return $result;
    }

    /**
     * Deletes an article (204 No Content).
     *
     * @throws LexwareException
     */
    public function delete(string $id): void
    {
        $this->client->sendRequest('articles/' . $id, null, 'DELETE');
    }

    /**
     * Lists articles, paginated.
     *
     * @param array<string, string> $filters  Erlaubt: articleNumber, gtin, type
     * @throws LexwareException
     */
    public function list(int $page = 0, array $filters = []): PagedResponse
    {
        $params = array_merge(['page' => (string) $page], $filters);
        $query  = http_build_query($params);

        $data = $this->client->sendRequest('articles?' . $query);
        return PagedResponse::fromArray($data);
    }
}
