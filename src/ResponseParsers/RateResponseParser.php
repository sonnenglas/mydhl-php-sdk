<?php

declare(strict_types=1);

namespace Sonnenglas\MyDHL\ResponseParsers;



class RateResponseParser
{
    public function __construct(protected array $response)
    {
    }

    public function parse(): array
    {
        return $this->response;
    }
}
