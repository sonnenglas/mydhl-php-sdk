<?php

declare(strict_types=1);

use Sonnenglas\MyDHL\MyDHL;
use Sonnenglas\MyDHL\ValueObjects\Account;
use Sonnenglas\MyDHL\ValueObjects\Address;
use Sonnenglas\MyDHL\ValueObjects\Contact;
use Sonnenglas\MyDHL\ValueObjects\CustomerTypeCode;
use Sonnenglas\MyDHL\ValueObjects\DangerousGood;
use Sonnenglas\MyDHL\ValueObjects\Incoterm;
use Sonnenglas\MyDHL\ValueObjects\Package;
use Sonnenglas\MyDHL\ValueObjects\Pickup;
use Sonnenglas\MyDHL\ValueObjects\ShipmentRequest;
use Sonnenglas\MyDHL\ValueObjects\ValueAddedService;

$myDhl = new MyDHL('username', 'password', testMode: true);

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

$valueAddedService = new ValueAddedService(
    serviceCode: 'HD',
    dangerousGood: new DangerousGood(
        contentId: '966',
        customDescription: 'Lithium ion batteries in compliance with Section II of PI 966 - 1 package',
    ),
);

$request = new ShipmentRequest(
    plannedShippingDateAndTime: new DateTimeImmutable('tomorrow'),
    productCode: 'U',
    shipperAddress: $shipperAddress,
    shipperContact: $shipperContact,
    receiverAddress: $receiverAddress,
    receiverContact: $receiverContact,
    accounts: [new Account(typeCode: 'shipper', number: '123456789')],
    packages: [new Package(weight: 5, height: 50, length: 10, width: 20)],
    pickup: new Pickup(
        isRequested: true,
        closeTime: '16:00',
        location: 'reception',
        address: $pickupAddress,
        contact: $pickupContact,
    ),
    description: 'Shipment description',
    getRateEstimates: false,
    incoterm: new Incoterm('EXW'),
    shipperTypeCode: new CustomerTypeCode('business'),
    receiverTypeCode: new CustomerTypeCode('private'),
    valueAddedServices: [$valueAddedService],
);

$shipment = $myDhl->getShipmentService()->createShipment($request);

print_r($shipment);
