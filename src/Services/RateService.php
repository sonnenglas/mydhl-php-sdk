<?php

declare(strict_types=1);

namespace Sonnenglas\MyDHL\Services;

use Sonnenglas\MyDHL\Client;
use Sonnenglas\MyDHL\Exceptions\ClientException;
use Sonnenglas\MyDHL\Exceptions\TotalPriceNotFoundException;
use Sonnenglas\MyDHL\ResponseParsers\RateResponseParser;
use Sonnenglas\MyDHL\ValueObjects\Rate;
use Sonnenglas\MyDHL\ValueObjects\RateRequest;

class RateService
{
    private const RETRIEVE_RATES_ONE_PIECE_URL = 'rates';

    /** @var array<string, mixed> */
    private array $lastResponse = [];

    public function __construct(private readonly Client $client)
    {
    }

    /**
     * @return list<Rate>
     * @throws ClientException
     * @throws TotalPriceNotFoundException
     */
    public function getRates(RateRequest $request): array
    {
        $this->lastResponse = $this->client->get(self::RETRIEVE_RATES_ONE_PIECE_URL, $request->toQuery());

        return (new RateResponseParser($this->lastResponse))->parse();
    }

    /**
     * @return array<string, mixed>
     */
    public function getLastRawResponse(): array
    {
        return $this->lastResponse;
    }
}
