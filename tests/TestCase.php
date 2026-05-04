<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use ReflectionClass;
use ReflectionException;

abstract class TestCase extends BaseTestCase
{
    /**
     * Helper for invoking private/protected methods in tests.
     *
     * @param array<int, mixed> $params
     * @throws ReflectionException
     */
    protected function executePrivateMethod(
        object $object,
        string $methodName,
        array $params = [],
    ): mixed {
        $reflection = new ReflectionClass($object::class);
        $method = $reflection->getMethod($methodName);

        return $method->invokeArgs($object, $params);
    }

    /**
     * Loads a JSON fixture as a decoded associative array.
     *
     * @return array<string, mixed>
     */
    protected static function loadJsonFixture(string $relativePath): array
    {
        $contents = file_get_contents(__DIR__ . '/' . $relativePath);
        if ($contents === false) {
            self::fail("Fixture not readable: {$relativePath}");
        }

        $decoded = json_decode($contents, true, flags: JSON_THROW_ON_ERROR);
        if (!is_array($decoded)) {
            self::fail("Fixture is not a JSON object: {$relativePath}");
        }

        /** @var array<string, mixed> $decoded */
        return $decoded;
    }
}
