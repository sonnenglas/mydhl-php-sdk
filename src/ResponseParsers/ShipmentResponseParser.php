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
            packages: $response['packages'],
            documents: $response['documents'],
            shipmentDetails: $response['shipmentDetails'],
            shipmentCharges: $response['shipmentCharges'],
        );
    }
}
