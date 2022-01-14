<?php

declare(strict_types=1);

namespace Sonnenglas\MyDHL\Responses;

use Psr\Http\Message\ResponseInterface;

class RateResponse
{
    public function __construct(protected ResponseInterface $response)
    {

    }

    public function getResponse(): array
    {
        return json_decode((string) $this->response->getBody(), true);
    }
}
