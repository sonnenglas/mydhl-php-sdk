<?php

declare(strict_types=1);

use Sonnenglas\MyDHL\MyDHL;
use Sonnenglas\MyDHL\ValueObjects\Account;
use Sonnenglas\MyDHL\ValueObjects\Address;
use Sonnenglas\MyDHL\ValueObjects\Contact;
use Sonnenglas\MyDHL\ValueObjects\CustomerTypeCode;
use Sonnenglas\MyDHL\ValueObjects\Incoterm;
use Sonnenglas\MyDHL\ValueObjects\Package;

$testMode = true;

$myDhl = new MyDHL('username', 'password', $testMode);

$shipmentService = $myDhl->getShipmentService();

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

$accounts = [];
$accounts[] = new Account(
    typeCode: 'shipper',
    number: '123456789',
);

$packages = [];
$packages[] = new Package(
    weight: 5,
    height: 50,
    length: 10,
    width: 20,
);

$plannedShippingDateAndTime = new DateTimeImmutable('tomorrow');
$productCode = 'U';
$isPickupRequested = true;
$description = 'Shipment description';
$incoterm = new Incoterm('EXW');

$shipperTypeCode = new CustomerTypeCode('business');
$receiverTypeCode = new CustomerTypeCode('private');

$shipment = $shipmentService->setPickup($isPickupRequested, '16:00', 'reception')
    ->setPlannedShippingDateAndTime($plannedShippingDateAndTime)
    ->setPickupDetails($pickupAddress, $pickupContact)
    ->setProductCode($productCode)
    ->setAccounts($accounts)
    ->setShipperDetails($shipperAddress, $shipperContact)
    ->setReceiverDetails($receiverAddress, $receiverContact)
    ->setShipperTypeCode($shipperTypeCode)
    ->setReceiverTypeCode($receiverTypeCode)
    ->setGetRateEstimates(false)
    ->setPackages($packages)
    ->setDescription($description)
    ->setIncoterm($incoterm)
    ->createShipment();

print_r($shipment);
