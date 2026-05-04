<?php

declare(strict_types=1);

namespace Tests\Integration;

use DateTimeImmutable;
use GuzzleHttp\Exception\BadResponseException;
use Sonnenglas\MyDHL\ValueObjects\Account;
use Sonnenglas\MyDHL\ValueObjects\Address;
use Sonnenglas\MyDHL\ValueObjects\Contact;
use Sonnenglas\MyDHL\ValueObjects\Package;
use Sonnenglas\MyDHL\ValueObjects\PickupRequest;
use Sonnenglas\MyDHL\ValueObjects\PickupShipmentSummary;

final class PickupServiceTest extends IntegrationTestCase
{
    public function testBookAndCancelPickup(): void
    {
        $request = new PickupRequest(
            plannedPickupDateAndTime: new DateTimeImmutable('+2 weekdays 14:00'),
            accounts: [new Account(typeCode: 'shipper', number: $this->accountNumber)],
            shipperAddress: new Address(
                addressLine1: 'Karl-Liebknecht-Straße 13',
                countryCode: 'DE',
                postalCode: '10178',
                cityName: 'Berlin',
            ),
            shipperContact: new Contact(
                phone: '+49301234567',
                companyName: 'Acme Lab',
                fullName: 'John Shipper',
                email: 'shipper@example.com',
            ),
            shipmentDetails: [
                new PickupShipmentSummary(
                    productCode: 'N',
                    isCustomsDeclarable: false,
                    packages: [new Package(weight: 5, height: 20, length: 10, width: 30)],
                ),
            ],
            closeTime: '18:00',
            location: 'reception',
            locationType: PickupRequest::LOCATION_BUSINESS,
        );

        try {
            $booking = $this->myDhl->getPickupService()->book($request);
        } catch (BadResponseException $e) {
            $body = (string) $e->getResponse()->getBody();
            // 8003 means the sandbox account isn't enabled for the Pickup service.
            // Treat that as "feature not available on this credential set" rather than a SDK bug.
            if (str_contains($body, '8003')) {
                self::markTestSkipped('Sandbox credentials not authorized for Pickup service: ' . $body);
            }

            self::fail('Unexpected pickup booking failure: ' . $body);
        }

        $confirmation = $booking->getFirstConfirmationNumber();
        self::assertNotNull($confirmation);
        self::assertNotSame('', $confirmation);

        // Clean up the courier slot we just booked.
        $this->myDhl->getPickupService()->cancel(
            dispatchConfirmationNumber: $confirmation,
            requestorName: 'SDK Integration Test',
            reason: 'wrongdate',
        );
    }
}
