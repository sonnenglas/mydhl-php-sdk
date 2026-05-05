<?php

declare(strict_types=1);

namespace Sonnenglas\MyDHL\Services;

use Sonnenglas\MyDHL\Client;
use Sonnenglas\MyDHL\Exceptions\ClientException;
use Sonnenglas\MyDHL\Exceptions\InvalidArgumentException;
use Sonnenglas\MyDHL\ResponseParsers\DocumentResponseParser;
use Sonnenglas\MyDHL\ValueObjects\DhlDocument;

/**
 * `GET /shipments/{trackingNumber}/get-image` — re-download archived
 * documents (waybill, customs invoice, transport-accompanying-document, ...)
 * for a shipment that was already created.
 *
 * Note: this endpoint does NOT return the transport label. Labels are only
 * returned inline at shipment creation time (`ShipmentService::createShipment`).
 */
class ImageService
{
    public const TYPE_WAYBILL = 'waybill';
    public const TYPE_COMMERCIAL_INVOICE = 'commercial-invoice';
    public const TYPE_CUSTOMS_ENTRY = 'customs-entry';
    public const TYPE_TRANSPORT_ACCOMPANYING_DOCUMENT = 'transport-accompanying-document';
    public const TYPE_GENERIC_ENTRY_SUMMARY = 'generic-entry-summary';
    public const TYPE_DHL_ISSUED_PROFORMA_INVOICE = 'dhl-issued-proforma-invoice';

    /** @var array<string, mixed> */
    private array $lastResponse = [];

    public function __construct(private readonly Client $client)
    {
    }

    /**
     * @param list<string> $typeCodes Document types to retrieve, e.g. ['label', 'commercial-invoice'].
     * @return list<DhlDocument>
     * @throws ClientException
     */
    public function getImages(
        string $shipmentTrackingNumber,
        string $shipperAccountNumber,
        array $typeCodes,
        string $pickupYearAndMonth,
        string $encodingFormat = 'pdf',
        bool $allInOnePDF = false,
    ): array {
        if ($shipmentTrackingNumber === '') {
            throw new InvalidArgumentException('shipmentTrackingNumber must not be empty.');
        }

        if ($shipperAccountNumber === '') {
            throw new InvalidArgumentException('shipperAccountNumber must not be empty.');
        }

        if ($typeCodes === []) {
            throw new InvalidArgumentException('typeCodes must not be empty.');
        }

        if (preg_match('/^\d{4}-\d{2}$/', $pickupYearAndMonth) !== 1) {
            throw new InvalidArgumentException('pickupYearAndMonth must be in YYYY-MM format.');
        }

        $uri = 'shipments/' . rawurlencode($shipmentTrackingNumber) . '/get-image';

        $this->lastResponse = $this->client->get($uri, [
            'shipperAccountNumber' => $shipperAccountNumber,
            'typeCode' => implode(',', $typeCodes),
            'pickupYearAndMonth' => $pickupYearAndMonth,
            'encodingFormat' => $encodingFormat,
            'allInOnePDF' => $allInOnePDF ? 'true' : 'false',
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
