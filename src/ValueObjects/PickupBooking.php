<?php

declare(strict_types=1);

namespace Sonnenglas\MyDHL\ValueObjects;

/**
 * Response value object for `POST /pickups`. The DHL API can consolidate
 * multiple bookings into a single courier visit, so confirmation numbers
 * come back as a list.
 */
final class PickupBooking
{
    /**
     * @param list<string> $dispatchConfirmationNumbers
     * @param list<string> $warnings
     */
    public function __construct(
        public readonly array $dispatchConfirmationNumbers,
        public readonly ?string $readyByTime,
        public readonly ?string $nextPickupDate,
        public readonly array $warnings = [],
    ) {
    }

    public function getFirstConfirmationNumber(): ?string
    {
        return $this->dispatchConfirmationNumbers[0] ?? null;
    }
}
