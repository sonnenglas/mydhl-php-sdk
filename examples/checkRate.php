<?php

declare(strict_types=1);

use Sonnenglas\MyDHL\MyDHL;
use Sonnenglas\MyDHL\ValueObjects\RateAddress;
use Sonnenglas\MyDHL\ValueObjects\Package;

$testMode = true;

$myDhl = new MyDHL('username', 'password', $testMode);

$rateService = $myDhl->getRateService();

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
    width: 30, // cm
);

$shippingDate = new DateTimeImmutable('now');

$rates = $rateService->setAccountNumber('99999999')
    ->setOriginAddress($originAddress)
    ->setDestinationAddress($destinationAddress)
    ->setPlannedShippingDate($shippingDate)
    ->setPackage($package)
    ->setNextBusinessDay(false)
    ->setCustomsDeclarable(false)
    ->getRates();

print_r($rates);
