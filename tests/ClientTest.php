<?php

declare(strict_types=1);

namespace Tests;

use Sonnenglas\MyDHL\Client;

final class ClientTest extends TestCase
{
    public function testTestMode(): void
    {
        $client = new Client('user', 'pass', testMode: true);

        self::assertStringContainsString('test', $client->getBaseUri());
    }
}
