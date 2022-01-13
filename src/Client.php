<?php

declare(strict_types=1);

namespace Sonnenglas\MyDHL;

class Client
{
    protected const URI_TEST = 'https://express.api.dhl.com/mydhlapi/test';

    protected const URI_PRODUCTION = 'https://express.api.dhl.com/mydhlapi';

    protected string $baseUri;

    public function __construct(
        protected string $username,
        protected string $password,
        protected bool $testMode = false
    ) {
        $this->baseUri = $this->testMode ? self::URI_TEST : self::URI_PRODUCTION;
    }

    public function getBaseUri(): string
    {
        return $this->baseUri;
    }
}
