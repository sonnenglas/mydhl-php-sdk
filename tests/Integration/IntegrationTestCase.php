<?php

declare(strict_types=1);

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use Sonnenglas\MyDHL\MyDHL;

/**
 * Base class for tests that hit the live DHL Express sandbox.
 *
 * Skipped automatically when DHL_EXPRESS_USERNAME / DHL_EXPRESS_PASSWORD
 * / DHL_EXPRESS_ACCOUNT_NUMBER are not set, so unit-only environments
 * (CI without secrets, fresh checkouts) stay green.
 *
 * Sandbox quota is 500 calls/day per credential set — keep test count low.
 */
abstract class IntegrationTestCase extends TestCase
{
    protected MyDHL $myDhl;
    protected string $accountNumber;

    protected function setUp(): void
    {
        parent::setUp();

        $username = self::env('DHL_EXPRESS_USERNAME');
        $password = self::env('DHL_EXPRESS_PASSWORD');
        $account = self::env('DHL_EXPRESS_ACCOUNT_NUMBER');

        if ($username === null || $password === null || $account === null) {
            self::markTestSkipped(
                'DHL Express sandbox credentials not configured. '
                . 'Set DHL_EXPRESS_USERNAME / DHL_EXPRESS_PASSWORD / DHL_EXPRESS_ACCOUNT_NUMBER to run.',
            );
        }

        $this->myDhl = new MyDHL($username, $password, testMode: true);
        $this->accountNumber = $account;
    }

    private static function env(string $name): ?string
    {
        $value = getenv($name);

        return is_string($value) && $value !== '' ? $value : null;
    }
}
