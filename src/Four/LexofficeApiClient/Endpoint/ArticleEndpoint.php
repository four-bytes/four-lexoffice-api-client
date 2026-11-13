<?php

declare(strict_types=1);

namespace Four\LexofficeApiClient\Endpoint;

use Four\LexofficeApiClient\DTO\Article;
use Four\LexofficeApiClient\DTO\PagedResponse;
use Four\LexofficeApiClient\Exception\LexofficeException;
use Four\LexofficeApiClient\LexofficeClient;

class ArticleEndpoint implements ArticleEndpointInterface
{
    public function __construct(
        private readonly LexofficeClient $client,
    ) {
    }

    /**
     * Creates a new article.
     *
     * @return array<string, mixed>  {id, resourceUri, version}
     * @throws LexofficeException
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
     * @throws LexofficeException
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
     * @throws LexofficeException
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
     * @throws LexofficeException
     */
    public function delete(string $id): void
    {
        $this->client->sendRequest('articles/' . $id, null, 'DELETE');
    }

    /**
     * Lists articles, paginated.
     *
     * @param array<string, string> $filters  Erlaubt: articleNumber, gtin, type
     * @throws LexofficeException
     */
    public function list(int $page = 0, array $filters = []): PagedResponse
    {
        $params = array_merge(['page' => (string) $page], $filters);
        $query  = http_build_query($params);

        $data = $this->client->sendRequest('articles?' . $query);
        return PagedResponse::fromArray($data);
    }
}
