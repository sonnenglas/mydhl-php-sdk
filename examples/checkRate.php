<?php

declare(strict_types=1);

use Sonnenglas\MyDHL\MyDHL;
use Sonnenglas\MyDHL\ValueObjects\Package;
use Sonnenglas\MyDHL\ValueObjects\RateAddress;
use Sonnenglas\MyDHL\ValueObjects\RateRequest;

$myDhl = new MyDHL('username', 'password', testMode: true);

$originAddress = new RateAddress(
    countryCode: 'DE',
    postalCode: '10117',
    cityName: 'Berlin',
);

$destinationAddress = new RateAddress(
    countryCode: 'DE',
    postalCode: '20099',
    cityName: 'Hamburg',
);

$package = new Package(
    weight: 10, // kg
    height: 20, // cm
    length: 10, // cm
    width: 30,  // cm
);

$request = new RateRequest(
    accountNumber: '99999999',
    originAddress: $originAddress,
    destinationAddress: $destinationAddress,
    package: $package,
    shippingDate: new DateTimeImmutable('now'),
    isCustomsDeclarable: false,
    nextBusinessDay: false,
);

$rates = $myDhl->getRateService()->getRates($request);

print_r($rates);
