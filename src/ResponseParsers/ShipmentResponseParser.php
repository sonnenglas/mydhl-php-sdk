<?php

declare(strict_types=1);

namespace Sonnenglas\MyDHL\ResponseParsers;

use Sonnenglas\MyDHL\ValueObjects\Shipment;

class ShipmentResponseParser
{
    public function parse(array $response): Shipment
    {


        return new Shipment(
            url: $response['url'],
            shipmentTrackingNumber: $response['shipmentTrackingNumber'],
            cancelPickupUrl: $response['cancelPickupUrl'],
            trackingUrl: $response['trackingUrl'],
            dispatchConfirmationNumber: $response['dispatchConfirmationNumber'],
            warnings: $response['warnings'],
            labelPdf: $this->getLabelPdf($response),
            packages: $response['packages'],
            documents: $response['documents'],
            shipmentDetails: $response['shipmentDetails'],
            shipmentCharges: $response['shipmentCharges'],
        );
    }

    public function getLabelPdf(array $response): string
    {
        $labelPdf = '';
        foreach ($response['documents'] as $document) {
            if ($document['typeCode'] === 'label' && $document['imageFormat'] === 'PDF') {
                $labelPdf = base64_decode($document['content'], true);
            }
        }

        return $labelPdf;
    }
}
