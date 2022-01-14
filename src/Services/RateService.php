<?php

declare(strict_types=1);

namespace Sonnenglas\MyDHL\Services;

use DateTimeImmutable;
use Sonnenglas\MyDHL\Client;
use Sonnenglas\MyDHL\Exceptions\MissingParameterException;
use Sonnenglas\MyDHL\Responses\RateResponse;
use Sonnenglas\MyDHL\ValueObjects\Package;
use Sonnenglas\MyDHL\ValueObjects\RateAddress;

class RateService
{
    private Package $package;
    private RateAddress $destinationAddress;
    private RateAddress $originAddress;
    private DateTimeImmutable $shippingDate;
    private string $accountNumber;

    // predefined defaults
    private bool $isCustomsDeclarable = false;
    private bool $nextBusinessDay = false;
    protected string $unitOfMeasurement = 'metric';

    private array $requiredParameters = [
        'accountNumber',
        'destinationAddress',
        'originAddress',
        'package',
        'shippingDate',
    ];

    public function __construct(private Client $client)
    {
    }

    /**
     * @throws MissingParameterException
     */
    public function getRates(): RateResponse
    {
        $this->validateParams();
        $query = $this->prepareQuery();
        $response = $this->client->get($query);
        return new RateResponse($response);
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
        $this->destinationAddress = $address;

        return $this;
    }

    public function setPackage(Package $package): RateService
    {
        $this->package = $package;

        return $this;
    }

    public function setPlannedShippingDate(DateTimeImmutable $date): RateService
    {
        $this->shippingDate = $date;

        return $this;
    }

    public function setCustomsDeclarable(bool $isCustomsDeclarable): RateService
    {
        $this->isCustomsDeclarable = $isCustomsDeclarable;

        return $this;
    }


    public function setNextBusinessDay(bool $nextBusinessDay): RateService
    {
        $this->nextBusinessDay = $nextBusinessDay;

        return $this;
    }

    /**
     * @throws MissingParameterException
     */
    protected function validateParams(): void
    {
        foreach ($this->requiredParameters as $param) {
            if (!isset($this->{$param})) {
                throw new MissingParameterException("Missing parameter: {$param}");
            }
        }
    }

    protected function prepareQuery(): array
    {
        return [
            'accountNumber' => $this->accountNumber,
            'originCountryCode ' => $this->originAddress->getCountryCode(),
            'originPostalCode' => $this->originAddress->getPostalCode(),
            'originCityName' => $this->originAddress->getCityName(),
            'destinationCountryCode ' => $this->destinationAddress->getCountryCode(),
            'destinationPostalCode' => $this->destinationAddress->getPostalCode(),
            'destinationCityName' => $this->destinationAddress->getCityName(),
            'weight' => $this->package->getWeight(),
            'length' => $this->package->getLength(),
            'height' => $this->package->getHeight(),
            'width' => $this->package->getWidth(),
            'plannedShippingDate' => $this->shippingDate->format('Y-m-d'),
            'isCustomsDeclarable' => $this->isCustomsDeclarable,
            'unitOfMeasurement ' => $this->unitOfMeasurement,
            'nextBusinessDay' => $this->nextBusinessDay,
        ];
    }
}