# Upgrading from 1.x to 2.0

The 2.0 release brings the SDK in line with the **DHL Express MyDHL API 3.2.2** specification. Most of 1.x continues to work — the changes below are either pure additions, bug fixes that align with what the live API actually requires, or thin behavioural cleanups around niche edge cases.

## Why

`POST /shipments` made under 1.x against the current sandbox returns

```
HTTP 422 — required key [pickup] not found
```

…even when no pickup is wanted. 2.0 fixes this and the other bugs surfaced by re-validating the SDK against the latest spec, plus expands coverage to **tracking, pickup booking/cancellation, document re-download and proof-of-delivery**.

## Breaking changes

### 1. `pickup` is now always sent on `POST /shipments`

The DHL spec marks `pickup` as **required** on the create-shipment request. 1.x silently omitted it when `Pickup::isRequested === false`, which the sandbox now rejects.

`Pickup::toQuery()` now returns `array<string, mixed>` (never `null`):

```php
// Pickup::notRequested() now serialises as { "isRequested": false }
// instead of being skipped — no caller change required, but the wire
// payload now always includes a top-level "pickup" key.
```

If you serialised `Pickup::toQuery()` directly anywhere, expect a non-null result.

### 2. `Shipment` response VO — nullable fields

`dispatchConfirmationNumber`, `cancelPickupUrl`, `trackingUrl` are now `?string` (nullable). Sandbox proved they're only present when a pickup is actually booked.

```php
// Before (1.x)
$shipment->getDispatchConfirmationNumber(); // string

// After (2.0)
$shipment->getDispatchConfirmationNumber(); // ?string
```

Update any callers that assumed these were always populated.

### 3. `Package` dimensions: `int` → `float`

The spec says dimensions are `number minimum 0.001`. Sending integers always worked; fractional dims (`12.5 cm`) used to be rejected because the SDK widened them to int.

```php
new Package(
    weight: 5,
    height: 20.5,   // now allowed
    length: 10,
    width: 30,
);
```

`Package::getHeight()`, `getLength()`, `getWidth()` now return `float`.

### 4. `Incoterm` is required when `isCustomsDeclarable=true`

Empty-string incoterm used to be accepted by 1.x and silently sent. The spec rejects empty incoterms, and `isCustomsDeclarable=true` requires one. The constructor now throws `MissingArgumentException` if you set `isCustomsDeclarable=true` without an `Incoterm`.

### 5. `declaredValue` + `declaredValueCurrency` required for customs

Setting `isCustomsDeclarable=true` without these used to silently produce a request DHL rejected at customs validation time. Now the `ShipmentRequest` constructor enforces both at object-build time.

### 6. `enableMockServer()` removed

The `api-mock.dhl.com` mock URL is not part of DHL's official documentation. Use `testMode: true` (the test/sandbox environment) instead.

```php
// Before
$myDhl = new MyDHL($u, $p);
$myDhl->enableMockServer();

// After
$myDhl = new MyDHL($u, $p, testMode: true);
```

### 7. `Client` request methods (`get`, `post`) now require `array` payload

Already `array` in 1.x; new `delete()` and `patch()` methods follow the same shape. Internal change — only relevant if you extended `Client`.

## Additions (non-breaking)

### Tracking

```php
$shipment = $myDhl->getTrackingService()->track('1234567890');

// Or batched:
$shipments = $myDhl->getTrackingService()->trackBatch([
    '1234567890', '0987654321',
]);
```

### Pickup booking & cancellation

```php
$booking = $myDhl->getPickupService()->book(new PickupRequest(
    plannedPickupDateAndTime: new DateTimeImmutable('+1 day 14:00'),
    accounts: [new Account('shipper', '123456789')],
    shipperAddress: $shipperAddress,
    shipperContact: $shipperContact,
    shipmentDetails: [new PickupShipmentSummary(
        productCode: 'N',
        isCustomsDeclarable: false,
        packages: [$package],
    )],
    closeTime: '18:00',
    location: 'reception',
));

$myDhl->getPickupService()->cancel(
    dispatchConfirmationNumber: $booking->getFirstConfirmationNumber(),
    requestorName: 'John Smith',
    reason: 'wrongdate',
);
```

### Re-download archived documents

```php
$documents = $myDhl->getImageService()->getImages(
    shipmentTrackingNumber: '1234567890',
    shipperAccountNumber: '123456789',
    typeCodes: [ImageService::TYPE_WAYBILL, ImageService::TYPE_COMMERCIAL_INVOICE],
    pickupYearAndMonth: '2026-05',
);
```

> Note: `/get-image` does **not** return the transport label. Labels are returned inline only at `createShipment` time.

### Proof of delivery

```php
$pods = $myDhl->getProofOfDeliveryService()->getProofOfDelivery(
    shipmentTrackingNumber: '1234567890',
    shipperAccountNumber: '123456789',
);
```

### Customs / cross-border shipments

For non-domestic shipments DHL now requires `declaredValue`, `exportDeclaration` and (recommended) `registrationNumbers`:

```php
$request = new ShipmentRequest(
    // …existing args…
    isCustomsDeclarable: true,
    incoterm: new Incoterm('DAP'),
    shipperRegistrationNumbers: [
        new RegistrationNumber(
            typeCode: RegistrationNumber::TYPE_VAT,
            number: 'DE123456789',
            issuerCountryCode: 'DE',
        ),
    ],
    declaredValue: 50.0,
    declaredValueCurrency: 'EUR',
    exportDeclaration: new ExportDeclaration(
        lineItems: [new LineItem(
            number: 1,
            description: 'Glass jar',
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
);
```

### Customer references and label rendering

```php
$request = new ShipmentRequest(
    // …existing args…
    customerReferences: [new CustomerReference(
        value: 'PO-12345',
        typeCode: CustomerReference::TYPE_BUYER_ORDER,
    )],
    outputImageProperties: new OutputImageProperties(
        printerDPI: 300,
        encodingFormat: OutputImageProperties::ENCODING_PDF,
        imageOptions: [new ImageOption(
            typeCode: ImageOption::TYPE_LABEL,
            templateName: 'ECOM26_84_001',
        )],
    ),
);
```

## Internals

- `Client` now sends `x-version: 3.2.0` and `Message-Reference-Date` headers on every request.
- New `Client::delete()` and `Client::patch()` for the new services.
- All response parsers funnel `mixed`-typed JSON through a small `Internal\Cast` helper to keep PHPStan level 9 happy without sprinkling `(string)` casts.

## Sandbox notes

- DHL's test environment caps you at **500 calls/day** per credential set.
- The `/tracking` endpoints in sandbox **only resolve a hardcoded list** of test waybill numbers; shipments you create through `/shipments` in sandbox are not trackable.
- Some endpoints (`/get-image`, etc.) are only available with explicit DHL approval. The integration suite skips tests when DHL replies with `8032` (not authorized).
