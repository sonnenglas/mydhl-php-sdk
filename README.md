# mydhl-php-sdk

Unofficial PHP SDK for the **DHL Express MyDHL REST API** (currently aligned with spec **3.2.2**, April 2026).

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
| Customs / international shipments (export declaration)      | ✅         |
| Re-download archived shipment documents                     | ✅         |
| Electronic Proof of Delivery                                | ✅         |
| Upload updated customs docs for shipment                    | ❌         |
| Upload Commercial Invoice Data for shipment                 | ❌         |
| **TRACKING**                                                |           |
| Track a single DHL Express Shipment                         | ✅         |
| Track multiple DHL Express Shipments (batch)                | ✅         |
| **PICKUP**                                                  |           |
| Create a DHL Express pickup booking request                 | ✅         |
| Cancel a DHL Express pickup booking request                 | ✅         |
| Update pickup information                                   | ❌         |
| **IDENTIFIER**                                              |           |
| Allocate identifiers upfront                                | ❌         |
| **ADDRESS**                                                 |           |
| Validate DHL Express pickup/delivery capability             | ❌         |
| **INVOICE**                                                 |           |
| Upload Commercial Invoice data                              | ❌         |
| **SERVICE POINTS / REFERENCE DATA**                         |           |
| Look up servicepoints / reference data                      | ❌         |

## Design

The SDK splits responsibilities between **value objects** (immutable, validated request payloads) and **services** (thin transport that talk to DHL):

- `RateRequest`, `ShipmentRequest`, `PickupRequest`, `Pickup`, `ExportDeclaration`, … — immutable inputs, validated in their constructors.
- `RateService::getRates(RateRequest)` — returns `Rate[]`.
- `ShipmentService::createShipment(ShipmentRequest)` — returns `Shipment`.
- `TrackingService::track(...)` / `trackBatch(...)`
- `PickupService::book(...)` / `cancel(...)`
- `ImageService::getImages(...)` — re-download archived customs/waybill PDFs.
- `ProofOfDeliveryService::getProofOfDelivery(...)`

Every required field is a constructor parameter, so missing data fails at request-build time, not somewhere inside the API call.

## Quick start

```php
use Sonnenglas\MyDHL\MyDHL;

$myDhl = new MyDHL(
    username: getenv('DHL_EXPRESS_USERNAME'),
    password: getenv('DHL_EXPRESS_PASSWORD'),
    testMode: true, // false → production
);
```

The base URLs are baked in:

| Environment | URL                                              |
|-------------|--------------------------------------------------|
| Sandbox     | `https://express.api.dhl.com/mydhlapi/test/`     |
| Production  | `https://express.api.dhl.com/mydhlapi/`          |

> Sandbox is rate-limited to **500 calls/day per credential set**.

## Usage

### Retrieve rates

```php
use DateTimeImmutable;
use Sonnenglas\MyDHL\ValueObjects\Package;
use Sonnenglas\MyDHL\ValueObjects\RateAddress;
use Sonnenglas\MyDHL\ValueObjects\RateRequest;

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
    package: new Package(weight: 10, height: 20, length: 10, width: 30),
    shippingDate: new DateTimeImmutable('tomorrow'),
);

$rates = $myDhl->getRateService()->getRates($request);
```

### Create a domestic shipment

```php
use DateTimeImmutable;
use Sonnenglas\MyDHL\ValueObjects\Account;
use Sonnenglas\MyDHL\ValueObjects\Address;
use Sonnenglas\MyDHL\ValueObjects\Contact;
use Sonnenglas\MyDHL\ValueObjects\Incoterm;
use Sonnenglas\MyDHL\ValueObjects\Package;
use Sonnenglas\MyDHL\ValueObjects\Pickup;
use Sonnenglas\MyDHL\ValueObjects\ShipmentRequest;

$request = new ShipmentRequest(
    plannedShippingDateAndTime: new DateTimeImmutable('tomorrow 14:00'),
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
    accounts: [new Account(typeCode: 'shipper', number: '123456789')],
    packages: [new Package(weight: 5, height: 20, length: 10, width: 30)],
    pickup: Pickup::notRequested(),
    incoterm: new Incoterm('DAP'),
);

$shipment = $myDhl->getShipmentService()->createShipment($request);
file_put_contents('label.pdf', $shipment->getLabelPdf());
```

### Customs / international shipments

International shipments need `declaredValue`, an `Incoterm`, an `ExportDeclaration` with line items, and (recommended) a VAT/EORI/IOSS `RegistrationNumber`:

```php
use Sonnenglas\MyDHL\ValueObjects\CustomerReference;
use Sonnenglas\MyDHL\ValueObjects\ExportDeclaration;
use Sonnenglas\MyDHL\ValueObjects\Invoice;
use Sonnenglas\MyDHL\ValueObjects\LineItem;
use Sonnenglas\MyDHL\ValueObjects\OutputImageProperties;
use Sonnenglas\MyDHL\ValueObjects\RegistrationNumber;

$request = new ShipmentRequest(
    // …same shipper / receiver / packages as above…
    productCode: 'P', // EXPRESS WORLDWIDE
    isCustomsDeclarable: true,
    incoterm: new Incoterm('DAP'),
    shipperRegistrationNumbers: [
        new RegistrationNumber(
            typeCode: RegistrationNumber::TYPE_VAT,
            number: 'DE123456789',
            issuerCountryCode: 'DE',
        ),
    ],
    customerReferences: [
        new CustomerReference(value: 'PO-12345', typeCode: CustomerReference::TYPE_BUYER_ORDER),
    ],
    declaredValue: 50.0,
    declaredValueCurrency: 'EUR',
    exportDeclaration: new ExportDeclaration(
        lineItems: [new LineItem(
            number: 1,
            description: 'Glass jar with embedded solar panel',
            price: 50.0,
            quantityValue: 1,
            quantityUnit: LineItem::UNIT_PIECES,
            manufacturerCountry: 'DE',
            netWeight: 2.0,
            grossWeight: 2.5,
            exportReasonType: LineItem::REASON_PERMANENT,
        )],
        invoice: new Invoice(
            number: 'INV-1001',
            date: new DateTimeImmutable('today'),
        ),
    ),
    outputImageProperties: new OutputImageProperties(
        printerDPI: 300,
        encodingFormat: OutputImageProperties::ENCODING_PDF,
    ),
);
```

### Track a shipment

```php
$tracked = $myDhl->getTrackingService()->track('1234567890');

if ($tracked !== null) {
    echo $tracked->status, "\n";
    foreach ($tracked->events as $event) {
        echo $event->date, ' ', $event->time, ' — ', $event->description, "\n";
    }
}

// Batch — DHL accepts hundreds of waybills per call.
$tracked = $myDhl->getTrackingService()->trackBatch([
    '1234567890', '0987654321',
]);
```

### Book / cancel a courier pickup separately

Use this when the shipment was created with `Pickup::notRequested()` and the pickup needs to be booked (or cancelled) independently — typical when an order is cancelled hours before pickup time.

```php
use Sonnenglas\MyDHL\ValueObjects\PickupRequest;
use Sonnenglas\MyDHL\ValueObjects\PickupShipmentSummary;

$booking = $myDhl->getPickupService()->book(new PickupRequest(
    plannedPickupDateAndTime: new DateTimeImmutable('+1 day 14:00'),
    accounts: [new Account('shipper', '123456789')],
    shipperAddress: $shipperAddress,
    shipperContact: $shipperContact,
    shipmentDetails: [new PickupShipmentSummary(
        productCode: 'N',
        isCustomsDeclarable: false,
        packages: [new Package(weight: 5, height: 20, length: 10, width: 30)],
    )],
    closeTime: '18:00',
    location: 'reception',
    locationType: PickupRequest::LOCATION_BUSINESS,
));

$myDhl->getPickupService()->cancel(
    dispatchConfirmationNumber: $booking->getFirstConfirmationNumber(),
    requestorName: 'John Smith',
    reason: 'wrongdate',
);
```

### Re-download archived documents (waybill, customs invoice)

```php
use Sonnenglas\MyDHL\Services\ImageService;

$documents = $myDhl->getImageService()->getImages(
    shipmentTrackingNumber: '1234567890',
    shipperAccountNumber: '123456789',
    typeCodes: [ImageService::TYPE_WAYBILL, ImageService::TYPE_COMMERCIAL_INVOICE],
    pickupYearAndMonth: '2026-05',
);

foreach ($documents as $doc) {
    file_put_contents("{$doc->typeCode}.pdf", $doc->content);
}
```

> `/get-image` does **not** return the transport label. The label is returned inline only at `createShipment` time. Save `Shipment::getLabelPdf()` then.

### Proof of Delivery

```php
$pods = $myDhl->getProofOfDeliveryService()->getProofOfDelivery(
    shipmentTrackingNumber: '1234567890',
    shipperAccountNumber: '123456789',
);
```

Full examples:

- [Check Rate](examples/checkRate.php)
- [Create Shipment](examples/createShipment.php)

## Development

```bash
composer install
composer test              # unit tests (PHPUnit)
composer test:integration  # live sandbox tests (require DHL_EXPRESS_* env vars)
composer phpstan           # PHPStan level 9
composer lint              # PHP-CS-Fixer (dry run)
composer lint:fix          # PHP-CS-Fixer (apply fixes)
```

### Integration tests against the DHL sandbox

Copy `tests/Integration/.env.example` and export your sandbox credentials, then:

```bash
DHL_EXPRESS_USERNAME=… \
DHL_EXPRESS_PASSWORD=… \
DHL_EXPRESS_ACCOUNT_NUMBER=… \
composer test:integration
```

Without these env vars the integration suite **auto-skips**, so contributor laptops and CI without secrets stay green. Each integration run consumes one or two of the daily 500 sandbox calls — keep them deliberate.

## Upgrading

- **From 1.x → 2.0:** see [`UPGRADE-1.x-to-2.0.md`](UPGRADE-1.x-to-2.0.md). Most callers only need to update the `Shipment` response getters that became nullable; international shipments need the new `ExportDeclaration` / `declaredValue` arguments.
- **From 0.x → 1.0:** the fluent setter API on services was replaced by immutable Request VOs. See the 1.0 release notes.

## Credits

Built and maintained by [Przemek Peron](mailto:przemek@sonnenglas.net).

## License

[MIT](LICENSE)
