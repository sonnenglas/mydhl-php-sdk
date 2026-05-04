<?php

declare(strict_types=1);

namespace Tests\Integration;

use DateTimeImmutable;
use Sonnenglas\MyDHL\ValueObjects\Account;
use Sonnenglas\MyDHL\ValueObjects\Address;
use Sonnenglas\MyDHL\ValueObjects\Contact;
use Sonnenglas\MyDHL\ValueObjects\Incoterm;
use Sonnenglas\MyDHL\ValueObjects\Package;
use Sonnenglas\MyDHL\ValueObjects\Pickup;
use Sonnenglas\MyDHL\ValueObjects\ShipmentRequest;

final class ShipmentServiceTest extends IntegrationTestCase
{
    /**
     * Domestic DE→DE shipment without pickup — the simplest valid scenario.
     */
    public function testCreatesDomesticShipmentWithoutPickup(): void
    {
        $request = new ShipmentRequest(
            plannedShippingDateAndTime: new DateTimeImmutable('+2 weekdays 14:00'),
            productCode: 'N',
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
            receiverAddress: new Address(
                addressLine1: 'Hamburger Str. 1',
                countryCode: 'DE',
                postalCode: '20099',
                cityName: 'Hamburg',
            ),
            receiverContact: new Contact(
                phone: '+49401234567',
                companyName: 'Acme Hamburg',
                fullName: 'Jane Receiver',
                email: 'receiver@example.com',
            ),
            accounts: [new Account(typeCode: 'shipper', number: $this->accountNumber)],
            packages: [new Package(weight: 5, height: 20, length: 10, width: 30)],
            pickup: Pickup::notRequested(),
            description: 'SDK integration test',
            isCustomsDeclarable: false,
            incoterm: new Incoterm('DAP'),
        );

        $shipment = $this->myDhl->getShipmentService()->createShipment($request);

        self::assertNotSame('', $shipment->getShipmentTrackingNumber());
        self::assertGreaterThan(10000, strlen($shipment->getLabelPdf()), 'Sandbox should return a real-looking PDF.');
        // Without pickup, DHL omits these — assert their absence so we notice if behaviour shifts.
        self::assertNull($shipment->getDispatchConfirmationNumber());
        self::assertNull($shipment->getCancelPickupUrl());
    }
}
