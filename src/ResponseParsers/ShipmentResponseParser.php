<?php

declare(strict_types=1);

namespace Sonnenglas\MyDHL\ResponseParsers;

use Sonnenglas\MyDHL\Internal\Cast;
use Sonnenglas\MyDHL\ValueObjects\Shipment;

final class ShipmentResponseParser
{
    /**
     * @param array<string, mixed> $response
     */
    public function __construct(private readonly array $response)
    {
    }

    public function parse(): Shipment
    {
        $documents = self::asListOfArrays($this->response['documents'] ?? []);

        return new Shipment(
            shipmentTrackingNumber: Cast::string($this->response['shipmentTrackingNumber']),
            labelPdf: $this->extractLabelPdf($documents),
            dispatchConfirmationNumber: self::optionalString($this->response['dispatchConfirmationNumber'] ?? null),
            cancelPickupUrl: self::optionalString($this->response['cancelPickupUrl'] ?? null),
            trackingUrl: self::optionalString($this->response['trackingUrl'] ?? null),
            warnings: self::asList($this->response['warnings'] ?? []),
            packages: self::asListOfArrays($this->response['packages'] ?? []),
            documents: $documents,
            shipmentDetails: self::asListOfArrays($this->response['shipmentDetails'] ?? []),
            shipmentCharges: self::asListOfArrays($this->response['shipmentCharges'] ?? []),
        );
    }

    private static function optionalString(mixed $value): ?string
    {
        return $value === null ? null : Cast::string($value);
    }

    /**
     * @param list<array<string, mixed>> $documents
     */
    private function extractLabelPdf(array $documents): string
    {
        foreach ($documents as $document) {
            if (($document['typeCode'] ?? null) === 'label' && ($document['imageFormat'] ?? null) === 'PDF') {
                $decoded = base64_decode(Cast::string($document['content']), true);
                if ($decoded !== false) {
                    return $decoded;
                }
            }
        }

        return '';
    }

    /**
     * @return list<mixed>
     */
    private static function asList(mixed $value): array
    {
        return is_array($value) ? array_values($value) : [];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function asListOfArrays(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        $result = [];
        foreach ($value as $item) {
            if (is_array($item)) {
                /** @var array<string, mixed> $item */
                $result[] = $item;
            }
        }

        return $result;
    }
}
