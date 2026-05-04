# mydhl-php-sdk

Unofficial PHP SDK for the **DHL Express MyDHL REST API**.

Status: [![CircleCI](https://circleci.com/gh/sonnenglas/mydhl-php-sdk/tree/master.svg?style=shield)](https://circleci.com/gh/sonnenglas/mydhl-api/tree/master)

> **Note:** Only the modern REST API is supported. The legacy SOAP API is not.

## Requirements

- PHP **^8.2**
- ext-json
- [`guzzlehttp/guzzle`](https://github.com/guzzle/guzzle) ^7.5
- [`ramsey/uuid`](https://github.com/ramsey/uuid) ^4.7

## Installation

```bash
composer require sonnenglas/mydhl-php-sdk
```

## Supported services

| Service                                                     | Supported |
|-------------------------------------------------------------|-----------|
| **RATING**                                                  |           |
| Retrieve Rates for a one-piece Shipment                     | ✅         |
| Retrieve Rates for Multi-piece Shipments                    | ❌         |
| Landed Cost                                                 | ❌         |
| **PRODUCT**                                                 |           |
| Retrieve DHL Express products                               | ❌         |
| **SHIPMENT**                                                |           |
| Create Shipment                                             | ✅         |
| Electronic Proof of Delivery                                | ❌         |
| Upload updated customs docs for shipment                    | ❌         |
| Upload Commercial Invoice Data for shipment                 | ❌         |
| **TRACKING**                                                |           |
| Track a single DHL Express Shipment                         | ❌         |
| Track single or multiple DHL Express Shipments              | ❌         |
| **PICKUP**                                                  |           |
| Cancel a DHL Express pickup booking request                 | ❌         |
| Update pickup information                                   | ❌         |
| Create a DHL Express pickup booking request                 | ❌         |
| **IDENTIFIER**                                              |           |
| Allocate identifiers upfront                                | ❌         |
| **ADDRESS**                                                 |           |
| Validate DHL Express pickup/delivery capability             | ❌         |
| **INVOICE**                                                 |           |
| Upload Commercial Invoice data                              | ❌         |

## Design

The SDK splits responsibilities between **value objects** (immutable, validated request payloads) and **services** (thin transport that talk to DHL):

- `RateRequest`, `ShipmentRequest`, `Pickup` — immutable inputs, validated in their constructors.
- `RateService::getRates(RateRequest)` — returns `Rate[]`.
- `ShipmentService::createShipment(ShipmentRequest)` — returns `Shipment`.

This avoids fluent setter chains with hidden required fields: every required field is a constructor parameter, so missing data fails at request-build time, not somewhere inside the API call.

## Usage

### Retrieve rates

```php
use DateTimeImmutable;
use Sonnenglas\MyDHL\MyDHL;
use Sonnenglas\MyDHL\ValueObjects\Package;
use Sonnenglas\MyDHL\ValueObjects\RateAddress;
use Sonnenglas\MyDHL\ValueObjects\RateRequest;

$myDhl = new MyDHL('username', 'password', testMode: true);

$request = new RateRequest(
    accountNumber: '99999999',
    originAddress: new RateAddress(
        countryCode: 'DE',
        postalCode: '10117',
        cityName: 'Berlin',
    ),
    destinationAddress: new RateAddress(
        countryCode: 'DE',
        postalCode: '20099',
        cityName: 'Hamburg',
    ),
    package: new Package(
        weight: 10, // kg
        height: 20, // cm
        length: 10, // cm
        width: 30,  // cm
    ),
    shippingDate: new DateTimeImmutable('tomorrow'),
);

$rates = $myDhl->getRateService()->getRates($request);
```

### Create shipment

```php
use DateTimeImmutable;
use Sonnenglas\MyDHL\MyDHL;
use Sonnenglas\MyDHL\ValueObjects\Account;
use Sonnenglas\MyDHL\ValueObjects\Address;
use Sonnenglas\MyDHL\ValueObjects\Contact;
use Sonnenglas\MyDHL\ValueObjects\Incoterm;
use Sonnenglas\MyDHL\ValueObjects\Package;
use Sonnenglas\MyDHL\ValueObjects\Pickup;
use Sonnenglas\MyDHL\ValueObjects\ShipmentRequest;

$myDhl = new MyDHL('username', 'password', testMode: true);

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
);

$receiverContact = new Contact(
    phone: '+48687777777',
    companyName: 'Acme Lab',
    fullName: 'John Doe',
    email: 'receiver@test.com',
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
    pickup: Pickup::notRequested(),
    description: 'Shipment description',
    incoterm: new Incoterm('EXW'),
);

$shipment = $myDhl->getShipmentService()->createShipment($request);
```

To request a pickup, replace `Pickup::notRequested()` with a fully-populated `Pickup`:

```php
new Pickup(
    isRequested: true,
    closeTime: '16:00',
    location: 'reception',
    address: $pickupAddress,
    contact: $pickupContact,
)
```

Full examples:

- [Check Rate](examples/checkRate.php)
- [Create Shipment](examples/createShipment.php)

## Development

```bash
composer install
composer test     # PHPUnit
composer phpstan  # PHPStan level 9
composer lint     # PHP-CS-Fixer (dry run)
composer lint:fix # PHP-CS-Fixer (apply fixes)
```

## Upgrading from 0.x

The 1.0 release replaces the fluent setter API on services with immutable request value objects:

```php
// Before (0.x)
$rateService->setAccountNumber('...')
    ->setOriginAddress($origin)
    ->setDestinationAddress($dest)
    ->setPackage($package)
    ->setPlannedShippingDate($date)
    ->getRates();

// After (1.x)
$rateService->getRates(new RateRequest(
    accountNumber: '...',
    originAddress: $origin,
    destinationAddress: $dest,
    package: $package,
    shippingDate: $date,
));
```

The same pattern applies to `ShipmentService::createShipment(ShipmentRequest)`.

Pickup details, previously set on `ShipmentService` via `setPickup()`/`setPickupDetails()`, now live in a single `Pickup` value object passed via `ShipmentRequest::$pickup`.

## License

[MIT](LICENSE)
