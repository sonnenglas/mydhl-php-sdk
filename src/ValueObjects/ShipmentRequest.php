<?php

declare(strict_types=1);

namespace Sonnenglas\MyDHL\ValueObjects;

use DateTimeImmutable;
use Sonnenglas\MyDHL\Exceptions\InvalidArgumentException;
use Sonnenglas\MyDHL\Exceptions\MissingArgumentException;

final class ShipmentRequest
{
    public const UNIT_METRIC = 'metric';
    public const UNIT_IMPERIAL = 'imperial';

    /**
     * @param list<Account> $accounts
     * @param list<Package> $packages
     * @param list<ValueAddedService> $valueAddedServices
     * @param list<RegistrationNumber> $shipperRegistrationNumbers
     * @param list<RegistrationNumber> $receiverRegistrationNumbers
     * @param list<CustomerReference> $customerReferences
     */
    public function __construct(
        public readonly DateTimeImmutable $plannedShippingDateAndTime,
        public readonly string $productCode,
        public readonly Address $shipperAddress,
        public readonly Contact $shipperContact,
        public readonly Address $receiverAddress,
        public readonly Contact $receiverContact,
        public readonly array $accounts,
        public readonly array $packages,
        public readonly Pickup $pickup,
        public readonly string $description = '',
        public readonly string $localProductCode = '',
        public readonly bool $getRateEstimates = false,
        public readonly bool $isCustomsDeclarable = false,
        public readonly ?Incoterm $incoterm = null,
        public readonly ?CustomerTypeCode $shipperTypeCode = null,
        public readonly ?CustomerTypeCode $receiverTypeCode = null,
        public readonly array $valueAddedServices = [],
        public readonly string $unitOfMeasurement = self::UNIT_METRIC,
        public readonly array $shipperRegistrationNumbers = [],
        public readonly array $receiverRegistrationNumbers = [],
        public readonly array $customerReferences = [],
        public readonly ?ExportDeclaration $exportDeclaration = null,
        public readonly ?float $declaredValue = null,
        public readonly ?string $declaredValueCurrency = null,
        public readonly ?OutputImageProperties $outputImageProperties = null,
    ) {
        $this->assertItemsOfType($this->accounts, Account::class, 'accounts');
        $this->assertItemsOfType($this->packages, Package::class, 'packages');
        $this->assertItemsOfType($this->valueAddedServices, ValueAddedService::class, 'valueAddedServices');
        $this->assertItemsOfType($this->shipperRegistrationNumbers, RegistrationNumber::class, 'shipperRegistrationNumbers');
        $this->assertItemsOfType($this->receiverRegistrationNumbers, RegistrationNumber::class, 'receiverRegistrationNumbers');
        $this->assertItemsOfType($this->customerReferences, CustomerReference::class, 'customerReferences');

        if ($this->productCode === '') {
            throw new MissingArgumentException('Missing argument: productCode');
        }

        if ($this->accounts === []) {
            throw new MissingArgumentException('Missing argument: at least one account');
        }

        if ($this->packages === []) {
            throw new MissingArgumentException('Missing argument: at least one package');
        }

        if ($this->shipperContact->getPhone() === '') {
            throw new MissingArgumentException('Missing phone number for shipper');
        }

        if ($this->receiverContact->getPhone() === '') {
            throw new MissingArgumentException('Missing phone number for receiver');
        }

        if ($this->isCustomsDeclarable && $this->incoterm === null) {
            throw new MissingArgumentException('Incoterm is required when isCustomsDeclarable is true.');
        }

        if ($this->isCustomsDeclarable && $this->declaredValue === null) {
            throw new MissingArgumentException('declaredValue is required when isCustomsDeclarable is true.');
        }

        if ($this->isCustomsDeclarable && $this->declaredValueCurrency === null) {
            throw new MissingArgumentException('declaredValueCurrency is required when isCustomsDeclarable is true.');
        }

        if (($this->declaredValue === null) !== ($this->declaredValueCurrency === null)) {
            throw new InvalidArgumentException('declaredValue and declaredValueCurrency must be set together.');
        }

        if (
            $this->unitOfMeasurement !== self::UNIT_METRIC
            && $this->unitOfMeasurement !== self::UNIT_IMPERIAL
        ) {
            throw new InvalidArgumentException(
                "Unit of measurement must be '" . self::UNIT_METRIC . "' or '" . self::UNIT_IMPERIAL . "'.",
            );
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function toQuery(): array
    {
        $query = [
            'plannedShippingDateAndTime' => $this->plannedShippingDateAndTime->format('Y-m-d\TH:i:s \G\M\TP'),
            'accounts' => array_map(static fn (Account $a): array => $a->getAsArray(), $this->accounts),
            'customerDetails' => [
                'shipperDetails' => $this->customerSection(
                    $this->shipperAddress,
                    $this->shipperContact,
                    $this->shipperTypeCode,
                    $this->shipperRegistrationNumbers,
                ),
                'receiverDetails' => $this->customerSection(
                    $this->receiverAddress,
                    $this->receiverContact,
                    $this->receiverTypeCode,
                    $this->receiverRegistrationNumbers,
                ),
            ],
            'content' => array_filter([
                'packages' => array_map(
                    static fn (Package $p): array => [
                        'weight' => $p->getWeight(),
                        'dimensions' => [
                            'length' => $p->getLength(),
                            'width' => $p->getWidth(),
                            'height' => $p->getHeight(),
                        ],
                    ],
                    $this->packages,
                ),
                'unitOfMeasurement' => $this->unitOfMeasurement,
                'isCustomsDeclarable' => $this->isCustomsDeclarable,
                'incoterm' => $this->incoterm !== null ? (string) $this->incoterm : null,
                'description' => $this->description,
                'declaredValue' => $this->declaredValue,
                'declaredValueCurrency' => $this->declaredValueCurrency,
                'exportDeclaration' => $this->exportDeclaration?->toArray(),
            ], static fn (mixed $v): bool => $v !== null),
            'getRateEstimates' => $this->getRateEstimates,
            'productCode' => $this->productCode,
            'pickup' => $this->pickup->toQuery(),
        ];

        if ($this->localProductCode !== '') {
            $query['localProductCode'] = $this->localProductCode;
        }

        if ($this->receiverContact->getEmail() !== '') {
            $query['shipmentNotification'] = [[
                'typeCode' => 'email',
                'languageCountryCode' => $this->receiverAddress->getCountryCode(),
                'receiverId' => $this->receiverContact->getEmail(),
            ]];
        }

        if ($this->valueAddedServices !== []) {
            $query['valueAddedServices'] = array_map(
                static fn (ValueAddedService $vas): array => $vas->getAsArray(),
                $this->valueAddedServices,
            );
        }

        if ($this->customerReferences !== []) {
            $query['customerReferences'] = array_map(
                static fn (CustomerReference $r): array => $r->toArray(),
                $this->customerReferences,
            );
        }

        if ($this->outputImageProperties !== null) {
            $imageProps = $this->outputImageProperties->toArray();
            if ($imageProps !== []) {
                $query['outputImageProperties'] = $imageProps;
            }
        }

        return $query;
    }

    /**
     * @param list<RegistrationNumber> $registrationNumbers
     * @return array<string, mixed>
     */
    private function customerSection(
        Address $address,
        Contact $contact,
        ?CustomerTypeCode $typeCode,
        array $registrationNumbers,
    ): array {
        $section = [
            'postalAddress' => $address->getAsArray(),
            'contactInformation' => $contact->getAsArray(),
        ];

        if ($typeCode !== null) {
            $section['typeCode'] = (string) $typeCode;
        }

        if ($registrationNumbers !== []) {
            $section['registrationNumbers'] = array_map(
                static fn (RegistrationNumber $r): array => $r->toArray(),
                $registrationNumbers,
            );
        }

        return $section;
    }

    /**
     * @param array<int, mixed> $items
     * @param class-string $expected
     */
    private function assertItemsOfType(array $items, string $expected, string $field): void
    {
        foreach ($items as $item) {
            if (!$item instanceof $expected) {
                throw new InvalidArgumentException(
                    "Field '{$field}' must contain only instances of {$expected}.",
                );
            }
        }
    }
}
