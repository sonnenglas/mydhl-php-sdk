<?php

declare(strict_types=1);

namespace Sonnenglas\MyDHL\ValueObjects;

use DateTimeImmutable;
use Exception;

final class TrackingEvent
{
    /**
     * @param list<array{code?: string, description?: string}> $serviceArea
     */
    public function __construct(
        public readonly string $date,
        public readonly string $time,
        public readonly ?string $gmtOffset,
        public readonly string $typeCode,
        public readonly string $description,
        public readonly ?string $signedBy,
        public readonly array $serviceArea,
    ) {
    }

    /**
     * Best-effort parsing of the date+time+offset triple as a DateTimeImmutable.
     * Returns null if the API didn't return enough data to construct one.
     *
     * @throws Exception
     */
    public function getOccurredAt(): ?DateTimeImmutable
    {
        if ($this->date === '' || $this->time === '') {
            return null;
        }

        return new DateTimeImmutable($this->date . ' ' . $this->time . ($this->gmtOffset ?? ''));
    }
}
