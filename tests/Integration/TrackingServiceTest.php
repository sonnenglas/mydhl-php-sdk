<?php

declare(strict_types=1);

namespace Tests\Integration;

/**
 * Sandbox tracking only resolves a hardcoded list of test waybill numbers
 * (documented in the OpenAPI spec for /tracking and /shipments/{tn}/tracking).
 * Live shipments created via the sandbox shipments endpoint are NOT trackable
 * — that's a sandbox limitation, not a bug in the SDK.
 */
final class TrackingServiceTest extends IntegrationTestCase
{
    private const SANDBOX_WAYBILL_A = '9356579890';
    private const SANDBOX_WAYBILL_B = '4818240420';

    public function testTracksSingleSandboxWaybill(): void
    {
        $shipment = $this->myDhl->getTrackingService()->track(self::SANDBOX_WAYBILL_A);

        self::assertNotNull($shipment);
        self::assertSame(self::SANDBOX_WAYBILL_A, $shipment->shipmentTrackingNumber);
        self::assertNotSame('', $shipment->status);
        self::assertNotEmpty($shipment->events);
    }

    public function testTrackBatchReturnsBothShipments(): void
    {
        $results = $this->myDhl->getTrackingService()->trackBatch([
            self::SANDBOX_WAYBILL_A,
            self::SANDBOX_WAYBILL_B,
        ]);

        self::assertCount(2, $results);
        $numbers = array_map(static fn ($s) => $s->shipmentTrackingNumber, $results);
        self::assertContains(self::SANDBOX_WAYBILL_A, $numbers);
        self::assertContains(self::SANDBOX_WAYBILL_B, $numbers);
    }
}
