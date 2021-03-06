<?php

declare(strict_types=1);

namespace Tests;

use Sonnenglas\MyDHL\Client;

class ClientTest extends TestCase
{
    public function testTestMode(): void
    {
        $testMode = true;
        $client = new Client('user', 'pass', $testMode);

        $this->assertStringContainsString('test', $client->getBaseUri());
    }
}
