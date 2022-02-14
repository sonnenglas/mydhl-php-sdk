<?php

declare(strict_types=1);

namespace Tests\Services;

use DateTimeImmutable;
use Sonnenglas\MyDHL\Client;
use Sonnenglas\MyDHL\Services\ShipmentService;
use Sonnenglas\MyDHL\ValueObjects\Account;
use Sonnenglas\MyDHL\ValueObjects\Address;
use Sonnenglas\MyDHL\ValueObjects\Contact;
use Sonnenglas\MyDHL\ValueObjects\Package;
use Tests\TestCase;

class ShipmentServiceTest extends TestCase
{

    public function testPrepareQuery()
    {
        $client = new Client('fakeUser', 'fakePass', true);
        $shipmentService = new ShipmentService($client);

        $pickupAddress = new Address(
            addressLine1: 'Karl-Liebknecht-Straße 14',
            countryCode: 'DE',
            postalCode: '10178',
            cityName: 'Berlin',
            addressLine2: 'second floor',
            provinceCode: 'Berlin',
        );

        $pickupContact = new Contact(
            phone: '+49689999999',
            companyName: 'Acme Lab',
            fullName: 'John Pickup',
        );

        $shipperAddress = new Address(
            addressLine1: 'Karl-Liebknecht-Straße 13',
            countryCode: 'DE',
            postalCode: '10178',
            cityName: 'Berlin',
        );

        $shipperContact = new Contact(
            phone: '+49688888888',
            companyName: 'Acme Lab',
            fullName: 'John Shipper',
            email: 'shipper@test.com',
        );

        $receiverAddress = new Address(
            addressLine1: 'Wroclawska 17',
            countryCode: 'PL',
            postalCode: '65-218',
            cityName: 'Zielona Gora',
            addressLine2: 'second floor',
            provinceCode: 'lubuskie',
        );

        $receiverContact = new Contact(
            phone: '+48687777777',
            companyName: 'Acme Lab',
            fullName: 'John Doe',
            email: 'receiver@test.com',
        );

        $accounts[] = new Account(
            typeCode: 'shipper',
            number: '123456789',
        );

        $packages[] = new Package(
            weight: 5,
            height: 50,
            length: 10,
            width: 20,
        );

        $plannedShippingDateAndTime = new DateTimeImmutable('tomorrow');
        $productCode = 'B';
        $localProductCode = 'C';
        $isPickupRequested = true;

        $shipmentService->setPickup($isPickupRequested, '18:00', 'reception')
            ->setPlannedShippingDateAndTime($plannedShippingDateAndTime)
            ->setPickupDetails($pickupAddress, $pickupContact)
            ->setProductCode($productCode)
            ->setLocalProductCode($localProductCode)
            ->setAccounts($accounts)
            ->setShipperDetails($shipperAddress, $shipperContact)
            ->setReceiverDetails($receiverAddress, $receiverContact)
            ->setGetRateEstimates(false)
            ->setPackages($packages);

        $result = $this->executePrivateMethod($shipmentService, 'prepareQuery', []);

        $this->assertEquals($plannedShippingDateAndTime->format(DateTimeImmutable::ATOM), $result['plannedShippingDateAndTime']);
        $this->assertEquals($accounts[0]->getNumber(), $result['accounts'][0]['number']);
        $this->assertEquals($shipperAddress->getPostalCode(), $result['customerDetails']['shipperDetails']['postalAddress']['postalCode']);
        $this->assertEquals($shipperContact->getEmail(), $result['customerDetails']['shipperDetails']['contactInformation']['email']);
        $this->assertEquals($receiverAddress->getAddressLine1(), $result['customerDetails']['receiverDetails']['postalAddress']['addressLine1']);
        $this->assertEquals($receiverContact->getFullName(), $result['customerDetails']['receiverDetails']['contactInformation']['fullName']);
        $this->assertEquals($pickupAddress->getAddressLine1(), $result['pickupDetails']['postalAddress']['addressLine1']);
        $this->assertEquals($pickupContact->getFullName(), $result['pickupDetails']['contactInformation']['fullName']);
        $this->assertEquals($packages[0]->getWeight(), $result['content']['packages'][0]['weight']);
        $this->assertEquals($packages[0]->getLength(), $result['content']['packages'][0]['dimensions']['length']);
        $this->assertEquals($productCode, $result['productCode']);
        $this->assertEquals($localProductCode, $result['localProductCode']);
        $this->assertEquals($receiverContact->getEmail(), $result['shipmentNotification']['receiverId']);
        $this->assertEquals('true', $result['pickup']['isRequested']);
    }
}
