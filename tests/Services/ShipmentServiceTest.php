<?php

declare(strict_types=1);

namespace Tests\Services;

use DateTimeImmutable;
use Sonnenglas\MyDHL\ValueObjects\Account;
use Sonnenglas\MyDHL\ValueObjects\Address;
use Sonnenglas\MyDHL\ValueObjects\Contact;
use Sonnenglas\MyDHL\ValueObjects\Incoterm;
use Sonnenglas\MyDHL\ValueObjects\Package;
use Sonnenglas\MyDHL\ValueObjects\Pickup;
use Sonnenglas\MyDHL\ValueObjects\ShipmentRequest;
use Tests\TestCase;

final class ShipmentServiceTest extends TestCase
{
    public function testRequestToQuery(): void
    {
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

        $accounts = [new Account(typeCode: 'shipper', number: '123456789')];
        $packages = [new Package(weight: 5, height: 50, length: 10, width: 20)];
        $plannedShippingDateAndTime = new DateTimeImmutable('tomorrow');
        $productCode = 'B';
        $localProductCode = 'C';
        $description = 'Shipment content';

        $request = new ShipmentRequest(
            plannedShippingDateAndTime: $plannedShippingDateAndTime,
            productCode: $productCode,
            shipperAddress: $shipperAddress,
            shipperContact: $shipperContact,
            receiverAddress: $receiverAddress,
            receiverContact: $receiverContact,
            accounts: $accounts,
            packages: $packages,
            pickup: new Pickup(
                isRequested: true,
                closeTime: '18:00',
                location: 'reception',
                address: $pickupAddress,
                contact: $pickupContact,
            ),
            description: $description,
            localProductCode: $localProductCode,
            getRateEstimates: false,
            incoterm: new Incoterm('EXW'),
        );

        $result = $request->toQuery();

        self::assertSame(
            $plannedShippingDateAndTime->format('Y-m-d\TH:i:s \G\M\TP'),
            $result['plannedShippingDateAndTime'],
        );

        /** @var list<array{number: string}> $accountsResult */
        $accountsResult = $result['accounts'];
        self::assertSame($accounts[0]->getNumber(), $accountsResult[0]['number']);

        /** @var array{shipperDetails: array{postalAddress: array<string, string>, contactInformation: array<string, string>}, receiverDetails: array{postalAddress: array<string, string>, contactInformation: array<string, string>}} $customerDetails */
        $customerDetails = $result['customerDetails'];
        self::assertSame(
            $shipperAddress->getPostalCode(),
            $customerDetails['shipperDetails']['postalAddress']['postalCode'],
        );
        self::assertSame(
            $shipperContact->getEmail(),
            $customerDetails['shipperDetails']['contactInformation']['email'],
        );
        self::assertSame(
            $receiverAddress->getAddressLine1(),
            $customerDetails['receiverDetails']['postalAddress']['addressLine1'],
        );
        self::assertSame(
            $receiverContact->getFullName(),
            $customerDetails['receiverDetails']['contactInformation']['fullName'],
        );

        /** @var array{isRequested: bool, pickupDetails: array{postalAddress: array<string, string>, contactInformation: array<string, string>}} $pickupResult */
        $pickupResult = $result['pickup'];
        self::assertTrue($pickupResult['isRequested']);
        self::assertSame(
            $pickupAddress->getAddressLine1(),
            $pickupResult['pickupDetails']['postalAddress']['addressLine1'],
        );
        self::assertSame(
            $pickupContact->getFullName(),
            $pickupResult['pickupDetails']['contactInformation']['fullName'],
        );

        /** @var array{packages: list<array{weight: float|int, dimensions: array{length: float|int, width: float|int, height: float|int}}>, description: string} $content */
        $content = $result['content'];
        self::assertSame($packages[0]->getWeight(), $content['packages'][0]['weight']);
        self::assertSame($packages[0]->getLength(), $content['packages'][0]['dimensions']['length']);
        self::assertSame($description, $content['description']);

        self::assertSame($productCode, $result['productCode']);
        self::assertSame($localProductCode, $result['localProductCode']);

        /** @var list<array{receiverId: string}> $shipmentNotification */
        $shipmentNotification = $result['shipmentNotification'];
        self::assertSame($receiverContact->getEmail(), $shipmentNotification[0]['receiverId']);
    }
}
