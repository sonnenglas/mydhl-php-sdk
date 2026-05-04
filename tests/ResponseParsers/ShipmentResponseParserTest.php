<?php

declare(strict_types=1);

namespace Tests\ResponseParsers;

use Sonnenglas\MyDHL\ResponseParsers\ShipmentResponseParser;
use Tests\TestCase;

final class ShipmentResponseParserTest extends TestCase
{
    public function testParse(): void
    {
        $jsonResponse = self::loadJsonFixture('fixtures/create_shipment_response.json');

        $shipmentResponseParser = new ShipmentResponseParser($jsonResponse);

        $shipment = $shipmentResponseParser->parse();

        self::assertSame('123456790', $shipment->getShipmentTrackingNumber());
        self::assertSame(
            'https://express.api.dhl.com/mydhlapi/shipments/1234567890/tracking',
            $shipment->getTrackingUrl(),
        );
        self::assertSame('PRG200227000256', $shipment->getDispatchConfirmationNumber());

        $packages = $shipment->getPackages();
        self::assertSame('JD914600003889482921', $packages[0]['trackingNumber']);

        $documents = $shipment->getDocuments();
        self::assertSame('label', $documents[0]['typeCode']);

        $shipmentDetails = $shipment->getShipmentDetails();
        /** @var array{shipperDetails: array{postalAddress: array{addressLine1: string}}} $customerDetails */
        $customerDetails = $shipmentDetails[0]['customerDetails'];
        self::assertSame('Na Cukrovaru 1063', $customerDetails['shipperDetails']['postalAddress']['addressLine1']);

        $shipmentCharges = $shipment->getShipmentCharges();
        self::assertSame(147, $shipmentCharges[0]['price']);

        $warnings = $shipment->getWarnings();
        self::assertSame("can't return prices", $warnings[0]);

        // Smoke test that the label PDF was actually decoded.
        self::assertGreaterThan(20000, strlen($shipment->getLabelPdf()));
    }
}
