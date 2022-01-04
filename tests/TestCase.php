<?php

declare(strict_types=1);

namespace Tests;

use Sonnenglas\MyDHLApi\Client;
use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected const TEST_USER = 'user';

    protected const TEST_PASS = 'password';

    protected Client $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = new Client(self::TEST_USER, self::TEST_PASS);
    }

    protected function tearDown(): void
    {
    }

}
