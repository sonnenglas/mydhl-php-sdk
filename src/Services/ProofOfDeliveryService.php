<?php

declare(strict_types=1);

namespace Sonnenglas\MyDHL\Services;

use Sonnenglas\MyDHL\Client;
use Sonnenglas\MyDHL\Exceptions\ClientException;
use Sonnenglas\MyDHL\Exceptions\InvalidArgumentException;
use Sonnenglas\MyDHL\ResponseParsers\DocumentResponseParser;
use Sonnenglas\MyDHL\ValueObjects\DhlDocument;

/**
 * `GET /shipments/{trackingNumber}/proof-of-delivery` — download the signed POD
 * once a shipment has been delivered.
 */
class ProofOfDeliveryService
{
    public const CONTENT_DETAIL = 'epod-detail';
    public const CONTENT_SUMMARY = 'epod-summary';
    public const CONTENT_TABLE = 'epod-table';
    public const CONTENT_DETAIL_ESIG = 'epod-detail-esig';

    /** @var array<string, mixed> */
    private array $lastResponse = [];

    public function __construct(private readonly Client $client)
    {
    }

    /**
     * @return list<DhlDocument>
     * @throws ClientException
     */
    public function getProofOfDelivery(
        string $shipmentTrackingNumber,
        string $shipperAccountNumber,
        string $contentType = self::CONTENT_DETAIL,
    ): array {
        if ($shipmentTrackingNumber === '') {
            throw new InvalidArgumentException('shipmentTrackingNumber must not be empty.');
        }

        if ($shipperAccountNumber === '') {
            throw new InvalidArgumentException('shipperAccountNumber must not be empty.');
        }

        $uri = 'shipments/' . rawurlencode($shipmentTrackingNumber) . '/proof-of-delivery';

        $this->lastResponse = $this->client->get($uri, [
            'shipperAccountNumber' => $shipperAccountNumber,
            'content' => $contentType,
        ]);

        return (new DocumentResponseParser($this->lastResponse))->parse();
    }

    /**
     * @return array<string, mixed>
     */
    public function getLastRawResponse(): array
    {
        return $this->lastResponse;
    }
}
