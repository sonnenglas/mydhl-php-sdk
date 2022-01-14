<?php

declare(strict_types=1);

namespace Sonnenglas\MyDHL;

use Sonnenglas\MyDHL\Services\RateService;

class MyDHL
{
    protected Client $client;

    public function __construct(
        string $username,
        string $password,
        bool $testMode = false
    ) {
        $this->client = new Client($username, $password, $testMode);
    }

    public function enableMockServer(): void
    {
        $this->client->enableMockServer();
    }

    public function getRateService(): RateService
    {
        return new RateService($this->client);
    }
}
