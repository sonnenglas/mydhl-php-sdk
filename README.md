# mydhl-api
PHP library for using DHL Express REST API (MyDHL API). 

Status: [![CircleCI](https://circleci.com/gh/sonnenglas/mydhl-php-sdk/tree/master.svg?style=shield)](https://circleci.com/gh/sonnenglas/mydhl-api/tree/master)

__Note:__ It supports only the latest REST API provided by DHL. No SOAP API support.


# Supported services:

| Service                                                     | Supported |
|-------------------------------------------------------------|-----------|
| __RATING__                                                  |           |
| Retrieve Rates for a one piece Shipment                     | ✅         |
| Retrieve Rates for Multi-piece Shipments                    | ❌         |
| Landed Cost                                                 | ❌         |
| __PRODUCT__                                                 |           |
| Retrieve DHL Express products                               | ❌         |
| __SHIPMENT__                                                |           |
| Electronic Proof of Delivery                                | ❌         |
| Upload updated customs docs for shipment                    | ❌         |
| Create Shipment                                             | ✅         |
| Upload Commercial Invoice Data for shipment                 | ❌         |
| __TRACKING__                                                |           |
| Track a single DHL Express Shipment                         | ❌         |
| Tracka single or multiple DHL Express Shipments             | ❌         |
| __PICKUP__                                                  |           |
| Cancel a DHL Express pickup booking request                 | ❌         |
| Update pickup information for existing pickup booking req   | ❌         |
| Create a DHL Express pickup booking request                 | ❌         |
| __IDENTIFIER__                                              |           |
| Service to allocate identifiers upfront ...                 | ❌         |
| __ADDRESS__                                                 |           |
| Validate DHL Express pickup/delivery capability             | ❌         |
| __INVOICE__                                                 |           |
| Upload Commercial Invoice data                              | ❌         |

# Usage:

```diff```

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
    height: 20, // cm
    length: 10, // cm
    width: 30, // cm
);

$shippingDate = new DateTimeImmutable('2021-01-15 12:00:00');

$rates = $rateService->setAccountNumber('99999999')
    ->setOriginAddress($originAddress)
    ->setDestinationAddress($destinationAddress)
    ->setPlannedShippingDate($shippingDate)
    ->setPackage($package)
    ->setNextBusinessDay(false)
    ->setCustomsDeclarable(false)
    ->getRates();

```


All usage examples:
- [Check Rate](https://github.com/sonnenglas/mydhl-php-sdk/blob/master/examples/checkRate.php)
- [Create Shipment](https://github.com/sonnenglas/mydhl-php-sdk/blob/master/examples/createShipment.php)
