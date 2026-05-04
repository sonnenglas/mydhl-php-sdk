<?php

declare(strict_types=1);

namespace Sonnenglas\MyDHL\ResponseParsers;

use Sonnenglas\MyDHL\Internal\Cast;
use Sonnenglas\MyDHL\ValueObjects\TrackedShipment;
use Sonnenglas\MyDHL\ValueObjects\TrackingEvent;

final class TrackingResponseParser
{
    /**
     * @param array<string, mixed> $response
     */
    public function __construct(private readonly array $response)
    {
    }

    /**
     * @return list<TrackedShipment>
     */
    public function parse(): array
    {
        $shipments = $this->response['shipments'] ?? [];
        if (!is_array($shipments)) {
            return [];
        }

        $result = [];
        foreach ($shipments as $shipment) {
            if (!is_array($shipment)) {
                continue;
            }
            /** @var array<string, mixed> $shipment */
            $result[] = $this->parseShipment($shipment);
        }

        return $result;
    }

    /**
     * @param array<string, mixed> $shipment
     */
    private function parseShipment(array $shipment): TrackedShipment
    {
        return new TrackedShipment(
            shipmentTrackingNumber: Cast::string($shipment['shipmentTrackingNumber'] ?? ''),
            status: Cast::string($shipment['status'] ?? ''),
            shipmentTimestamp: self::optionalString($shipment['shipmentTimestamp'] ?? null),
            productCode: self::optionalString($shipment['productCode'] ?? null),
            description: self::optionalString($shipment['description'] ?? null),
            totalWeight: self::optionalFloat($shipment['totalWeight'] ?? null),
            unitOfMeasurements: self::optionalString($shipment['unitOfMeasurements'] ?? null),
            events: $this->parseEvents($shipment['events'] ?? []),
            raw: $shipment,
        );
    }

    /**
     * @param mixed $events
     * @return list<TrackingEvent>
     */
    private function parseEvents(mixed $events): array
    {
        if (!is_array($events)) {
            return [];
        }

        $result = [];
        foreach ($events as $event) {
            if (!is_array($event)) {
                continue;
            }
            /** @var array<string, mixed> $event */
            $result[] = new TrackingEvent(
                date: Cast::string($event['date'] ?? ''),
                time: Cast::string($event['time'] ?? ''),
                gmtOffset: self::optionalString($event['GMTOffset'] ?? null),
                typeCode: Cast::string($event['typeCode'] ?? ''),
                description: Cast::string($event['description'] ?? ''),
                signedBy: self::optionalString($event['signedBy'] ?? null),
                serviceArea: self::parseServiceArea($event['serviceArea'] ?? []),
            );
        }

        return $result;
    }

    /**
     * @param mixed $area
     * @return list<array{code?: string, description?: string}>
     */
    private static function parseServiceArea(mixed $area): array
    {
        if (!is_array($area)) {
            return [];
        }

        $result = [];
        foreach ($area as $entry) {
            if (!is_array($entry)) {
                continue;
            }
            $code = isset($entry['code']) ? Cast::string($entry['code']) : null;
            $description = isset($entry['description']) ? Cast::string($entry['description']) : null;
            $shape = [];
            if ($code !== null) {
                $shape['code'] = $code;
            }
            if ($description !== null) {
                $shape['description'] = $description;
            }
            $result[] = $shape;
        }

        return $result;
    }

    private static function optionalString(mixed $value): ?string
    {
        return $value === null ? null : Cast::string($value);
    }

    private static function optionalFloat(mixed $value): ?float
    {
        return $value === null ? null : Cast::float($value);
    }
}
