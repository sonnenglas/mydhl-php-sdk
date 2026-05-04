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
    ) {
        $this->assertItemsOfType($this->accounts, Account::class, 'accounts');
        $this->assertItemsOfType($this->packages, Package::class, 'packages');
        $this->assertItemsOfType($this->valueAddedServices, ValueAddedService::class, 'valueAddedServices');

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
                'shipperDetails' => [
                    'postalAddress' => $this->shipperAddress->getAsArray(),
                    'contactInformation' => $this->shipperContact->getAsArray(),
                ],
                'receiverDetails' => [
                    'postalAddress' => $this->receiverAddress->getAsArray(),
                    'contactInformation' => $this->receiverContact->getAsArray(),
                ],
            ],
            'content' => [
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
                'incoterm' => (string) ($this->incoterm ?? ''),
                'description' => $this->description,
            ],
            'getRateEstimates' => $this->getRateEstimates,
            'productCode' => $this->productCode,
        ];

        if ($this->shipperTypeCode !== null) {
            $query['customerDetails']['shipperDetails']['typeCode'] = (string) $this->shipperTypeCode;
        }

        if ($this->receiverTypeCode !== null) {
            $query['customerDetails']['receiverDetails']['typeCode'] = (string) $this->receiverTypeCode;
        }

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

        $pickupQuery = $this->pickup->toQuery();
        if ($pickupQuery !== null) {
            $query['pickup'] = $pickupQuery;
        }

        if ($this->valueAddedServices !== []) {
            $query['valueAddedServices'] = array_map(
                static fn (ValueAddedService $vas): array => $vas->getAsArray(),
                $this->valueAddedServices,
            );
        }

        return $query;
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
