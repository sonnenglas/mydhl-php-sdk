<?php

declare(strict_types=1);

namespace Tests\ResponseParsers;

use Sonnenglas\MyDHL\ResponseParsers\ShipmentResponseParser;
use Sonnenglas\MyDHL\ValueObjects\Shipment;
use Tests\TestCase;

class ShipmentResponseParserTest extends TestCase
{
    public function testParse(): void
    {
        $jsonResponse = json_decode(file_get_contents(__DIR__ . "/../fixtures/create_shipment_response.json"), true);

        $shipmentResponseParser = new ShipmentResponseParser($jsonResponse);

        /** @var Shipment $shipment */
        $shipment = $shipmentResponseParser->parse();

        $this->assertEquals("https://express.api.dhl.com/mydhlapi/shipments", $shipment->getUrl());
        $this->assertEquals("123456790", $shipment->getShipmentTrackingNumber());
        $this->assertEquals("https://express.api.dhl.com/mydhlapi/shipments/1234567890/tracking", $shipment->getTrackingUrl());
        $this->assertEquals("PRG200227000256", $shipment->getDispatchConfirmationNumber());
        $this->assertEquals("JD914600003889482921", $shipment->getPackages()[0]['trackingNumber']);
        $this->assertEquals("label", $shipment->getDocuments()[0]['typeCode']);
        $this->assertEquals(
            "Na Cukrovaru 1063",
            $shipment->getShipmentDetails()[0]['customerDetails']['shipperDetails']['postalAddress']['addressLine1']
        );
        $this->assertEquals(147, $shipment->getShipmentCharges()[0]['price']);
        $this->assertEquals("can't return prices", $shipment->getWarnings()[0]);
        // Simple check whether the label pdf is big enough. This means it probably holds
        // a PDF file
        $this->assertTrue(strlen($shipment->getLabelPdf()) > 20000);
    }
}
