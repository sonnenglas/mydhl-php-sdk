<?php

declare(strict_types=1);

namespace Sonnenglas\MyDHL\ResponseParsers;

use Sonnenglas\MyDHL\Internal\Cast;
use Sonnenglas\MyDHL\ValueObjects\PickupBooking;

final class PickupResponseParser
{
    /**
     * @param array<string, mixed> $response
     */
    public function __construct(private readonly array $response)
    {
    }

    public function parse(): PickupBooking
    {
        return new PickupBooking(
            dispatchConfirmationNumbers: self::asStringList($this->response['dispatchConfirmationNumbers'] ?? []),
            readyByTime: self::optionalString($this->response['readyByTime'] ?? null),
            nextPickupDate: self::optionalString($this->response['nextPickupDate'] ?? null),
            warnings: self::asStringList($this->response['warnings'] ?? []),
        );
    }

    /**
     * @return list<string>
     */
    private static function asStringList(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        $result = [];
        foreach ($value as $item) {
            if (is_string($item)) {
                $result[] = $item;
            }
        }

        return $result;
    }

    private static function optionalString(mixed $value): ?string
    {
        return $value === null ? null : Cast::string($value);
    }
}
