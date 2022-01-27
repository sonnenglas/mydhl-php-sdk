<?php

declare(strict_types=1);

namespace Sonnenglas\MyDHL\ValueObjects;

use DateTimeImmutable;

class Rate
{
    public function __construct(
        private string $productName,
        private string $productCode,
        private string $localProductCode,
        private string $localProductCountryCode,
        private bool   $isCustomerAgreement,
        private float  $weightVolumetric,
        private float  $weightProvided,
        private float  $totalPrice,
        private string $currency,
        private DateTimeImmutable $estimatedDeliveryDateAndTime,
        private DateTimeImmutable $pricingDate,
    ) {
    }

    /**
     * Gets the product name
     * @return string
     */
    public function getProductName(): string
    {
        return $this->productName;
    }

    /**
     * Gets the product code
     * @return string
     */
    public function getProductCode(): string
    {
        return $this->productCode;
    }

    /**
     * Gets the local product code
     * @return string
     */
    public function getLocalProductCode(): string
    {
        return $this->localProductCode;
    }

    /**
     * Gets the local product country code
     * @return string
     */
    public function getLocalProductCountryCode(): string
    {
        return $this->localProductCountryCode;
    }

    public function getIsCustomerAgreement(): bool
    {
        return $this->isCustomerAgreement;
    }

    /**
     * Gets volumetric weight
     * @return float
     */
    public function getWeightVolumetric(): float
    {
        return $this->weightVolumetric;
    }

    /**
     * Gets provided weight
     * @return float
     */
    public function getWeightProvided(): float
    {
        return $this->weightProvided;
    }

    /**
     * Gets total price
     * @return float
     */
    public function getTotalPrice(): float
    {
        return $this->totalPrice;
    }

    /**
     * Gets currency code
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * Gets estimated delivery date and time
     * @return DateTimeImmutable
     */
    public function getEstimatedDeliveryDateAndTime(): DateTimeImmutable
    {
        return $this->estimatedDeliveryDateAndTime;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getPricingDate(): DateTimeImmutable
    {
        return $this->pricingDate;
    }
}
