<?php

declare(strict_types=1);

namespace Sonnenglas\MyDHL\Services;

use DateTimeImmutable;
use Sonnenglas\MyDHL\Client;
use Sonnenglas\MyDHL\Exceptions\MissingArgumentException;
use Sonnenglas\MyDHL\ResponseParsers\RateResponseParser;
use Sonnenglas\MyDHL\ValueObjects\Package;
use Sonnenglas\MyDHL\ValueObjects\Rate;
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

    private array $requiredArguments = [
        'accountNumber',
        'destinationAddress',
        'originAddress',
        'package',
        'shippingDate',
    ];

    private array $lastResponse;

    private const RETRIEVE_RATES_ONE_PIECE_URL = 'rates';

    public function __construct(private Client $client)
    {
    }

    /**
     * @return Rate[]
     * @throws MissingArgumentException
     * @throws \Sonnenglas\MyDHL\Exceptions\ClientException
     * @throws \Sonnenglas\MyDHL\Exceptions\TotalPriceNotFoundException
     */
    public function getRates(): array
    {
        $this->validateParams();
        $query = $this->prepareQuery();
        $this->lastResponse = $this->client->get(self::RETRIEVE_RATES_ONE_PIECE_URL, $query);
        return (new RateResponseParser($this->lastResponse))->parse();
    }

    public function getLastRawResponse(): array
    {
        return $this->lastResponse;
    }

    public function setAccountNumber(string $accountNumber): self
    {
        $this->accountNumber = $accountNumber;

        return $this;
    }

    public function setOriginAddress(RateAddress $address): self
    {
        $this->originAddress = $address;

        return $this;
    }

    public function setDestinationAddress(RateAddress $address): self
    {
        $this->destinationAddress = $address;

        return $this;
    }

    public function setPackage(Package $package): self
    {
        $this->package = $package;

        return $this;
    }

    public function setPlannedShippingDate(DateTimeImmutable $date): self
    {
        $this->shippingDate = $date;

        return $this;
    }

    public function setCustomsDeclarable(bool $isCustomsDeclarable): self
    {
        $this->isCustomsDeclarable = $isCustomsDeclarable;

        return $this;
    }


    public function setNextBusinessDay(bool $nextBusinessDay): self
    {
        $this->nextBusinessDay = $nextBusinessDay;

        return $this;
    }

    /**
     * @return void
     * @throws MissingArgumentException
     */
    private function validateParams(): void
    {
        foreach ($this->requiredArguments as $param) {
            if (!isset($this->{$param})) {
                throw new MissingArgumentException("Missing argument: {$param}");
            }
        }
    }

    private function prepareQuery(): array
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
            'isCustomsDeclarable' => $this->convertBoolToString($this->isCustomsDeclarable),
            'unitOfMeasurement' => $this->unitOfMeasurement,
            'nextBusinessDay' => $this->convertBoolToString($this->nextBusinessDay),
        ];
    }

    private function convertBoolToString(bool $value): string
    {
        return $value ? 'true' : 'false';
    }
}
