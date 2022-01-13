<?php

declare(strict_types=1);

namespace Sonnenglas\MyDHL\Services;


use Sonnenglas\MyDHL\Client;
use Sonnenglas\MyDHL\ValueObjects\Package;
use Sonnenglas\MyDHL\ValueObjects\RateAddress;
use Sonnenglas\MyDHL\ValueObjects\ShippingDate;

class RateService
{
    private bool $isCustomsDeclarable;
    private bool $nextBusinessDay;
    private Package $package;
    private RateAddress $destAddress;
    private RateAddress $originAddress;
    private ShippingDate $shippingDate;
    private string $accountNumber;
    protected string $unitOfMeasurement = 'metric';

    public function __construct(private Client $client)
    {

    }

    public function setAccountNumber(string $accountNumber): RateService
    {
        $this->accountNumber = $accountNumber;

        return $this;
    }

    public function setOriginAddress(RateAddress $address): RateService
    {
        $this->originAddress = $address;

        return $this;
    }

    public function setDestinationAddress(RateAddress $address): RateService
    {
        $this->destAddress = $address;

        return $this;
    }

    public function setPackage(Package $package): RateService
    {
        $this->package = $package;

        return $this;
    }

    public function setPlannedShippingDate(ShippingDate $date): RateAddress
    {
        $this->shippingDate = $date;

        return $this;
    }

    public function setCustomsDeclarable(bool $isCustomsDeclarable): RateAddress
    {
        $this->isCustomsDeclarable = $isCustomsDeclarable;

        return $this;
    }


    public function setNextBusinessDay(bool $nextBusinessDay): RateAddress
    {
        $this->nextBusinessDay = $nextBusinessDay;

        return $this;
    }



}
