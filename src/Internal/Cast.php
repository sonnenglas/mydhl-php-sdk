<?php

declare(strict_types=1);

namespace Sonnenglas\MyDHL\Internal;

use Sonnenglas\MyDHL\Exceptions\InvalidArgumentException;

/**
 * Strict casts from `mixed` (e.g. JSON-decoded payloads) to scalar types.
 *
 * @internal
 */
final class Cast
{
    public static function string(mixed $value, string $field = 'value'): string
    {
        if (is_string($value)) {
            return $value;
        }

        if (is_int($value) || is_float($value) || is_bool($value)) {
            return (string) $value;
        }

        throw new InvalidArgumentException("Expected string-castable {$field}, got " . get_debug_type($value));
    }

    public static function float(mixed $value, string $field = 'value'): float
    {
        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }

        if (is_string($value) && is_numeric($value)) {
            return (float) $value;
        }

        throw new InvalidArgumentException("Expected float-castable {$field}, got " . get_debug_type($value));
    }

    public static function bool(mixed $value): bool
    {
        return (bool) $value;
    }
}
