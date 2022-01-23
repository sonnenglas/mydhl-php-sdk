<?php

declare(strict_types=1);

namespace Sonnenglas\MyDHL\ValueObjects;

class Rate
{
    public function __construct(
        private string $productName,
        private string $productCode,
        private bool   $isCustomerAgreement,
        private float  $weightVolumetric,
        private float  $weightProvided,
        private float  $totalPrice,
        private string $currency,
    ) {
    }

    public function getProductName(): string
    {
        return $this->productName;
    }

    public function getProductCode(): string
    {
        return $this->productCode;
    }

    public function getIsCustomerAgreement(): bool
    {
        return $this->isCustomerAgreement;
    }

    public function getWeightVolumetric(): float
    {
        return $this->weightVolumetric;
    }

    public function getWeightProvided(): float
    {
        return $this->weightProvided;
    }

    public function getTotalPrice(): float
    {
        return $this->totalPrice;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }
}
