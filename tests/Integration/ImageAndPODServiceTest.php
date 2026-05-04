<?php

declare(strict_types=1);

namespace Tests\Integration;

use GuzzleHttp\Exception\ClientException as GuzzleClientException;
use Sonnenglas\MyDHL\Services\ImageService;

/**
 * Sandbox typically returns 404 for image / POD on the canned tracking
 * numbers because no documents were ever uploaded for them. We still want
 * to confirm the request lands without a 4xx-other / 5xx — a 404 with the
 * documented "no documents" body is acceptable.
 */
final class ImageAndPODServiceTest extends IntegrationTestCase
{
    private const SANDBOX_WAYBILL = '9356579890';

    public function testGetImageAtLeastReachesEndpoint(): void
    {
        try {
            $documents = $this->myDhl->getImageService()->getImages(
                shipmentTrackingNumber: self::SANDBOX_WAYBILL,
                shipperAccountNumber: $this->accountNumber,
                typeCodes: [ImageService::TYPE_WAYBILL],
                pickupYearAndMonth: date('Y-m'),
            );
            self::assertContainsOnlyInstancesOf(\Sonnenglas\MyDHL\ValueObjects\DhlDocument::class, $documents);
        } catch (GuzzleClientException $e) {
            self::assertEndpointReachable($e, 'get-image');
        }
    }

    public function testProofOfDeliveryAtLeastReachesEndpoint(): void
    {
        try {
            $documents = $this->myDhl->getProofOfDeliveryService()->getProofOfDelivery(
                shipmentTrackingNumber: self::SANDBOX_WAYBILL,
                shipperAccountNumber: $this->accountNumber,
            );
            self::assertContainsOnlyInstancesOf(\Sonnenglas\MyDHL\ValueObjects\DhlDocument::class, $documents);
        } catch (GuzzleClientException $e) {
            self::assertEndpointReachable($e, 'proof-of-delivery');
        }
    }

    /**
     * Treat the request as "reached the endpoint with a well-formed payload"
     * if DHL replies with one of the documented "no-data" / "not-enabled"
     * states. Anything else means the SDK built a malformed request.
     */
    private static function assertEndpointReachable(GuzzleClientException $e, string $context): void
    {
        $status = $e->getResponse()->getStatusCode();
        $body = (string) $e->getResponse()->getBody();

        if ($status === 403 && str_contains($body, '8032')) {
            self::markTestSkipped("Sandbox account not authorized for {$context}: {$body}");
        }

        self::assertSame(
            404,
            $status,
            "Unexpected status for {$context}: HTTP {$status} — {$body}",
        );
    }
}
