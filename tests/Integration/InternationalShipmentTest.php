<?php

declare(strict_types=1);

namespace Tests\Integration;

use DateTimeImmutable;
use Sonnenglas\MyDHL\ValueObjects\Account;
use Sonnenglas\MyDHL\ValueObjects\Address;
use Sonnenglas\MyDHL\ValueObjects\Contact;
use Sonnenglas\MyDHL\ValueObjects\CustomerReference;
use Sonnenglas\MyDHL\ValueObjects\ExportDeclaration;
use Sonnenglas\MyDHL\ValueObjects\Incoterm;
use Sonnenglas\MyDHL\ValueObjects\Invoice;
use Sonnenglas\MyDHL\ValueObjects\LineItem;
use Sonnenglas\MyDHL\ValueObjects\OutputImageProperties;
use Sonnenglas\MyDHL\ValueObjects\Package;
use Sonnenglas\MyDHL\ValueObjects\Pickup;
use Sonnenglas\MyDHL\ValueObjects\RegistrationNumber;
use Sonnenglas\MyDHL\ValueObjects\ShipmentRequest;

/**
 * Cross-border (DE → CH) customs-declarable shipment exercising all of the
 * Phase A-customs additions: declaredValue, exportDeclaration with line item
 * + invoice, registrationNumbers, customerReferences, outputImageProperties.
 *
 * Each run consumes 1 sandbox call against the daily 500-call quota.
 */
final class InternationalShipmentTest extends IntegrationTestCase
{
    public function testCreatesCustomsDeclarableShipmentToSwitzerland(): void
    {
        $request = new ShipmentRequest(
            plannedShippingDateAndTime: new DateTimeImmutable('+2 weekdays 14:00'),
            productCode: 'P', // EXPRESS WORLDWIDE — common non-doc cross-border code
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
                addressLine1: 'Bahnhofstrasse 1',
                countryCode: 'CH',
                postalCode: '8001',
                cityName: 'Zurich',
            ),
            receiverContact: new Contact(
                phone: '+41441234567',
                companyName: 'Acme Zurich',
                fullName: 'Anna Receiver',
                email: 'receiver@example.com',
            ),
            accounts: [new Account(typeCode: 'shipper', number: $this->accountNumber)],
            packages: [new Package(weight: 2.5, height: 15, length: 20, width: 20)],
            pickup: Pickup::notRequested(),
            description: 'SDK customs integration test',
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
                new CustomerReference(value: 'INT-TEST-' . date('YmdHis'), typeCode: CustomerReference::TYPE_BUYER_ORDER),
            ],
            declaredValue: 50.0,
            declaredValueCurrency: 'EUR',
            exportDeclaration: new ExportDeclaration(
                lineItems: [
                    new LineItem(
                        number: 1,
                        description: 'Glass jar with embedded solar panel',
                        price: 50.0,
                        quantityValue: 1,
                        quantityUnit: LineItem::UNIT_PIECES,
                        manufacturerCountry: 'DE',
                        netWeight: 2.0,
                        grossWeight: 2.5,
                        exportReasonType: LineItem::REASON_PERMANENT,
                    ),
                ],
                invoice: new Invoice(
                    number: 'INV-' . date('YmdHis'),
                    date: new DateTimeImmutable('today'),
                ),
                exportReasonType: LineItem::REASON_PERMANENT,
            ),
            outputImageProperties: new OutputImageProperties(
                printerDPI: 300,
                encodingFormat: OutputImageProperties::ENCODING_PDF,
            ),
        );

        $shipment = $this->myDhl->getShipmentService()->createShipment($request);

        self::assertNotSame('', $shipment->getShipmentTrackingNumber());
        self::assertGreaterThan(10000, strlen($shipment->getLabelPdf()));
    }
}
