# mydhl-api
PHP library for using DHL Express REST API (MyDHL API). 

Status: [![CircleCI](https://circleci.com/gh/sonnenglas/mydhl-api/tree/master.svg?style=shield)](https://circleci.com/gh/sonnenglas/mydhl-api/tree/master)

__Note:__ It supports only the latest REST API provided by DHL. No SOAP API support.

# Usage:

### Retrieve rates

```php
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
    width: 30, // cm
    height: 20, // cm
    length: 10, // cm
);

$shippingDate = new DateTimeImmutable('2021-01-15 12:00:00')

$rates = $rateService->setAccountNumber('99999999')
    ->setOriginAddress($originAddress)
    ->setDestinationAddress($destinationAddress)
    ->setPlannedShippingDate($shippingDate)
    ->setPackage($package)
    ->setNextBusinessDay(false)
    ->setCustomsDeclarable(false)
    ->getRates();

```
