<?php

declare(strict_types=1);

namespace Four\LexofficeApiClient\Exception;

class RateLimitException extends LexofficeException
{
    public function __construct(
        string $message = 'Rate limit exceeded after maximum retries',
        string $responseBody = '',
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, 429, $responseBody, $previous);
    }
}
