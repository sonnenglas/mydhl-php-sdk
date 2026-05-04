<?php

declare(strict_types=1);

namespace Sonnenglas\MyDHL\Services;

use Sonnenglas\MyDHL\Client;
use Sonnenglas\MyDHL\Exceptions\ClientException;
use Sonnenglas\MyDHL\Exceptions\InvalidArgumentException;
use Sonnenglas\MyDHL\ResponseParsers\TrackingResponseParser;
use Sonnenglas\MyDHL\ValueObjects\TrackedShipment;

final class TrackingService
{
    public const VIEW_ALL_CHECKPOINTS = 'all-checkpoints';
    public const VIEW_LAST_CHECKPOINT = 'last-checkpoint';
    public const VIEW_SHIPMENT_DETAILS = 'shipment-details';

    public const DETAIL_SHIPMENT = 'shipment';
    public const DETAIL_PIECE = 'piece';
    public const DETAIL_ALL = 'all';

    /** @var array<string, mixed> */
    private array $lastResponse = [];

    public function __construct(private readonly Client $client)
    {
    }

    /**
     * Track a single shipment by its waybill number.
     *
     * @throws ClientException
     */
    public function track(
        string $shipmentTrackingNumber,
        string $trackingView = self::VIEW_ALL_CHECKPOINTS,
        string $levelOfDetail = self::DETAIL_ALL,
    ): ?TrackedShipment {
        if ($shipmentTrackingNumber === '') {
            throw new InvalidArgumentException('shipmentTrackingNumber must not be empty.');
        }

        $uri = 'shipments/' . rawurlencode($shipmentTrackingNumber) . '/tracking';
        $this->lastResponse = $this->client->get($uri, [
            'trackingView' => $trackingView,
            'levelOfDetail' => $levelOfDetail,
        ]);

        $shipments = (new TrackingResponseParser($this->lastResponse))->parse();

        return $shipments[0] ?? null;
    }

    /**
     * Track up to a few hundred shipments in a single call.
     *
     * @param list<string> $shipmentTrackingNumbers
     * @return list<TrackedShipment>
     * @throws ClientException
     */
    public function trackBatch(
        array $shipmentTrackingNumbers,
        string $trackingView = self::VIEW_ALL_CHECKPOINTS,
        string $levelOfDetail = self::DETAIL_ALL,
    ): array {
        if ($shipmentTrackingNumbers === []) {
            throw new InvalidArgumentException('shipmentTrackingNumbers must not be empty.');
        }

        $this->lastResponse = $this->client->get('tracking', [
            'shipmentTrackingNumber' => implode(',', $shipmentTrackingNumbers),
            'trackingView' => $trackingView,
            'levelOfDetail' => $levelOfDetail,
        ]);

        return (new TrackingResponseParser($this->lastResponse))->parse();
    }

    /**
     * @return array<string, mixed>
     */
    public function getLastRawResponse(): array
    {
        return $this->lastResponse;
    }
}
