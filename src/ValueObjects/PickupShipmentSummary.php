<?php

declare(strict_types=1);

namespace Sonnenglas\MyDHL\ValueObjects;

use Sonnenglas\MyDHL\Exceptions\InvalidArgumentException;
use Sonnenglas\MyDHL\Exceptions\MissingArgumentException;

/**
 * Lightweight summary of a shipment included in a standalone pickup booking
 * (`POST /pickups`). Each pickup must reference at least one shipment (real
 * or to-be-created) by product code + dimensions.
 */
final class PickupShipmentSummary
{
    public const UNIT_METRIC = 'metric';
    public const UNIT_IMPERIAL = 'imperial';

    /**
     * @param array<int, mixed> $packages Validated at runtime to be a list of Package.
     */
    public function __construct(
        public readonly string $productCode,
        public readonly bool $isCustomsDeclarable,
        public readonly array $packages,
        public readonly string $unitOfMeasurement = self::UNIT_METRIC,
        public readonly ?string $shipmentTrackingNumber = null,
        public readonly ?float $declaredValue = null,
        public readonly ?string $declaredValueCurrency = null,
        public readonly ?string $localProductCode = null,
    ) {
        if ($productCode === '') {
            throw new MissingArgumentException('PickupShipmentSummary productCode must not be empty.');
        }

        if ($packages === []) {
            throw new MissingArgumentException('PickupShipmentSummary packages must not be empty.');
        }

        foreach ($packages as $package) {
            if (!$package instanceof Package) {
                throw new InvalidArgumentException('packages must contain only Package instances.');
            }
        }

        if ($unitOfMeasurement !== self::UNIT_METRIC && $unitOfMeasurement !== self::UNIT_IMPERIAL) {
            throw new InvalidArgumentException("unitOfMeasurement must be 'metric' or 'imperial'.");
        }

        if ($isCustomsDeclarable && ($declaredValue === null || $declaredValueCurrency === null)) {
            throw new MissingArgumentException(
                'declaredValue and declaredValueCurrency are required when isCustomsDeclarable is true.',
            );
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $packages = [];
        foreach ($this->packages as $p) {
            assert($p instanceof Package);
            $packages[] = [
                'weight' => $p->getWeight(),
                'dimensions' => [
                    'length' => $p->getLength(),
                    'width' => $p->getWidth(),
                    'height' => $p->getHeight(),
                ],
            ];
        }

        return array_filter([
            'productCode' => $this->productCode,
            'localProductCode' => $this->localProductCode,
            'isCustomsDeclarable' => $this->isCustomsDeclarable,
            'declaredValue' => $this->declaredValue,
            'declaredValueCurrency' => $this->declaredValueCurrency,
            'unitOfMeasurement' => $this->unitOfMeasurement,
            'shipmentTrackingNumber' => $this->shipmentTrackingNumber,
            'packages' => $packages,
        ], static fn (mixed $v): bool => $v !== null);
    }
}
