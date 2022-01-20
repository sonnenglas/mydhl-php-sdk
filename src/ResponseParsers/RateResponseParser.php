<?php

declare(strict_types=1);

namespace Sonnenglas\MyDHL\ResponseParsers;

use Sonnenglas\MyDHL\ValueObjects\Rate;

class RateResponseParser
{
    public function __construct(protected array $response)
    {
    }

    public function parse(): array
    {
        return $this->response;
    }

    /**
     * @param array $response
     * @return Rate[]
     */
    protected function extractRates(array $response): array
    {
        $rates = [];

        if (!isset($response['products']) || !is_iterable($response['products'])) {
            return $rates;
        }

        foreach ($response['products'] as $p) {
            $rates[] = $this->parseRate($p);
        }

        return $rates;
    }

    protected function parseRate(array $rate): Rate
    {
        return new Rate();
    }
}
