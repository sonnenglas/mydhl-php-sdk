<?php

declare(strict_types=1);

namespace Sonnenglas\MyDHL\ValueObjects;

use DateTimeImmutable;
use Sonnenglas\MyDHL\Exceptions\InvalidArgumentException;

final class RateRequest
{
    public const UNIT_METRIC = 'metric';
    public const UNIT_IMPERIAL = 'imperial';

    public function __construct(
        public readonly string $accountNumber,
        public readonly RateAddress $originAddress,
        public readonly RateAddress $destinationAddress,
        public readonly Package $package,
        public readonly DateTimeImmutable $shippingDate,
        public readonly bool $isCustomsDeclarable = false,
        public readonly bool $nextBusinessDay = false,
        public readonly string $unitOfMeasurement = self::UNIT_METRIC,
    ) {
        if ($this->accountNumber === '') {
            throw new InvalidArgumentException('Account number must not be empty.');
        }

        if ($this->unitOfMeasurement !== self::UNIT_METRIC && $this->unitOfMeasurement !== self::UNIT_IMPERIAL) {
            throw new InvalidArgumentException(
                "Unit of measurement must be '" . self::UNIT_METRIC . "' or '" . self::UNIT_IMPERIAL . "'.",
            );
        }
    }

    /**
     * @return array<string, string>
     */
    public function toQuery(): array
    {
        return [
            'accountNumber' => $this->accountNumber,
            'originCountryCode' => $this->originAddress->getCountryCode(),
            'originPostalCode' => $this->originAddress->getPostalCode(),
            'originCityName' => $this->originAddress->getCityName(),
            'destinationCountryCode' => $this->destinationAddress->getCountryCode(),
            'destinationPostalCode' => $this->destinationAddress->getPostalCode(),
            'destinationCityName' => $this->destinationAddress->getCityName(),
            'weight' => (string) $this->package->getWeight(),
            'length' => (string) $this->package->getLength(),
            'height' => (string) $this->package->getHeight(),
            'width' => (string) $this->package->getWidth(),
            'plannedShippingDate' => $this->shippingDate->format('Y-m-d'),
            'isCustomsDeclarable' => $this->isCustomsDeclarable ? 'true' : 'false',
            'unitOfMeasurement' => $this->unitOfMeasurement,
            'nextBusinessDay' => $this->nextBusinessDay ? 'true' : 'false',
        ];
    }
}
