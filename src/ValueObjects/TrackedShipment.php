<?php

declare(strict_types=1);

namespace Sonnenglas\MyDHL\ValueObjects;

final class TrackedShipment
{
    /**
     * @param list<TrackingEvent> $events
     * @param array<string, mixed> $raw Raw API payload for fields not yet modelled.
     */
    public function __construct(
        public readonly string $shipmentTrackingNumber,
        public readonly string $status,
        public readonly ?string $shipmentTimestamp,
        public readonly ?string $productCode,
        public readonly ?string $description,
        public readonly ?float $totalWeight,
        public readonly ?string $unitOfMeasurements,
        public readonly array $events,
        public readonly array $raw,
    ) {
    }

    public function getLatestEvent(): ?TrackingEvent
    {
        return $this->events[0] ?? null;
    }
}
