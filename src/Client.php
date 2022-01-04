<?php

declare(strict_types=1);

namespace Sonnenglas\MyDHLApi;

class Client
{
    protected const URL_TEST = 'https://express.api.dhl.com/mydhlapi/test';

    protected const URL_PRODUCTION = 'https://express.api.dhl.com/mydhlapi';

    protected string $baseUrl;

    public function __construct(
        protected string $username,
        protected string $password,
        protected bool $testMode = false
    ) {
        $this->baseUrl = $this->testMode ? self::URL_TEST : self::URL_PRODUCTION;
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }
}
