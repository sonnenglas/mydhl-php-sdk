<?php

declare(strict_types=1);

namespace Sonnenglas\MyDHL\Services;

use DateTimeImmutable;
use Sonnenglas\MyDHL\Client;
use Sonnenglas\MyDHL\Exceptions\MissingArgumentException;
use Sonnenglas\MyDHL\ResponseParsers\ShipmentResponseParser;
use Sonnenglas\MyDHL\ValueObjects\Account;
use Sonnenglas\MyDHL\ValueObjects\Address;
use Sonnenglas\MyDHL\ValueObjects\BuyerTypeCode;
use Sonnenglas\MyDHL\ValueObjects\Contact;
use Sonnenglas\MyDHL\Exceptions\InvalidArgumentException;
use Sonnenglas\MyDHL\ValueObjects\CustomerTypeCode;
use Sonnenglas\MyDHL\ValueObjects\Package;
use Sonnenglas\MyDHL\ValueObjects\Shipment;
use Sonnenglas\MyDHL\ValueObjects\Incoterm;

class ShipmentService
{
    private DateTimeImmutable $plannedShippingDateAndTime;

    private bool $isPickupRequested;
    private string $pickupCloseTime;
    private string $pickupLocation;
    private Address $pickupAddress;
    private Contact $pickupContact;
    private string $productCode;
    private string $localProductCode;
    private Address $shipperAddress;
    private Contact $shipperContact;
    private Address $receiverAddress;
    private Contact $receiverContact;
    private bool $getRateEstimates = false;
    private bool $isCustomsDeclarable = false;
    private string $description;
    private Incoterm $incoterm;
    private CustomerTypeCode $shipperTypeCode;
    private CustomerTypeCode $receiverTypeCode;

    protected string $unitOfMeasurement = 'metric';

    /**
     * @var array<Account>
     */
    private array $accounts;

    /**
     * @var array<Package>
     */
    private array $packages;

    private array $requiredArguments = [
        'plannedShippingDateAndTime',
        'isPickupRequested',
        'productCode',
        'shipperAddress',
        'shipperContact',
        'receiverAddress',
        'receiverContact',
        'accounts',
        'packages',
    ];

    private array $lastResponse;

    private const CREATE_SHIPMENT_URL = 'shipments';


    public function __construct(private Client $client)
    {
    }

    public function createShipment(): Shipment
    {
        $this->validateParams();
        $query = $this->prepareQuery();
        $this->lastResponse = $this->client->post(self::CREATE_SHIPMENT_URL, $query);
        return (new ShipmentResponseParser($this->lastResponse))->parse();
    }

    public function getLastRawResponse(): array
    {
        return $this->lastResponse;
    }

    public function setPlannedShippingDateAndTime(DateTimeImmutable $date): ShipmentService
    {
        $this->plannedShippingDateAndTime = $date;

        return $this;
    }

    public function setDescription(string $description): ShipmentService
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @param bool $isPickupRequested Please advise if a pickup is needed for this shipment
     * @param string $pickupCloseTime The latest time the location premises is available to dispatch the DHL Express shipment. (HH:MM)
     * @param string $pickupLocation Provides information on where the package should be picked up by DHL courier
     * @return $this
     */
    public function setPickup(bool $isPickupRequested, string $pickupCloseTime = '', string $pickupLocation = ''): ShipmentService
    {
        $this->isPickupRequested = $isPickupRequested;
        $this->pickupCloseTime = $pickupCloseTime;
        $this->pickupLocation = $pickupLocation;

        return $this;
    }

    public function isCustomsDeclarable(bool $isCustomsDeclarable): ShipmentService
    {
        $this->isCustomsDeclarable = $isCustomsDeclarable;

        return $this;
    }

    public function setShipperTypeCode(CustomerTypeCode $typeCode): ShipmentService
    {
        $this->shipperTypeCode = $typeCode;

        return $this;
    }

    public function setReceiverTypeCode(CustomerTypeCode $typeCode): ShipmentService
    {
        $this->receiverTypeCode = $typeCode;

        return $this;
    }


    public function setPickupDetails(Address $pickupAddress, Contact $pickupContact): ShipmentService
    {
        $this->pickupAddress = $pickupAddress;
        $this->pickupContact = $pickupContact;

        return $this;
    }

    public function setProductCode(string $productCode): ShipmentService
    {
        $this->productCode = $productCode;

        return $this;
    }

    public function setLocalProductCode(string $localProductCode): ShipmentService
    {
        $this->localProductCode = $localProductCode;

        return $this;
    }

    /**
     * @param array<Account> $accounts
     * @return $this
     * @throws InvalidArgumentException
     */
    public function setAccounts(array $accounts): ShipmentService
    {
        foreach ($accounts as $account) {
            if (!$account instanceof Account) {
                throw new InvalidArgumentException("Array should contain values of type Account");
            }
        }

        $this->accounts = $accounts;

        return $this;
    }

    public function setShipperDetails(Address $shipperAddress, Contact $shipperContact): ShipmentService
    {
        $this->shipperAddress = $shipperAddress;
        $this->shipperContact = $shipperContact;

        return $this;
    }

    public function setReceiverDetails(Address $receiverAddress, Contact $receiverContact): ShipmentService
    {
        $this->receiverAddress = $receiverAddress;
        $this->receiverContact = $receiverContact;

        return $this;
    }

    public function setGetRateEstimates(bool $getRateEstimates): ShipmentService
    {
        $this->getRateEstimates = $getRateEstimates;

        return $this;
    }

    /**
     * @param array<Package> $packages
     * @return $this
     * @throws InvalidArgumentException
     */
    public function setPackages(array $packages): ShipmentService
    {
        foreach ($packages as $package) {
            if (!$package instanceof Package) {
                throw new InvalidArgumentException("Array should contain values of type Package");
            }
        }

        $this->packages = $packages;

        return $this;
    }

    public function setIncoterm(Incoterm $incoterm): ShipmentService
    {
        $this->incoterm = $incoterm;

        return $this;
    }

    public function prepareQuery(): array
    {
        $query = [
            'plannedShippingDateAndTime' => $this->plannedShippingDateAndTime->format('Y-m-d\TH:i:s \G\M\TP'),
            'accounts' => $this->prepareAccountsQuery(),
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
                'packages' => $this->preparePackagesQuery(),
                'unitOfMeasurement' => $this->unitOfMeasurement,
                'isCustomsDeclarable' => $this->isCustomsDeclarable,
                'incoterm' => (string) $this->incoterm,
                'description' => $this->description,
            ],
            'getRateEstimates' => $this->getRateEstimates,
            'productCode' => $this->productCode,

        ];

        if (isset($this->shipperTypeCode)) {
            $query['customerDetails']['shipperDetails']['typeCode'] = (string) $this->shipperTypeCode;
        }

        if (isset($this->receiverTypeCode)) {
            $query['customerDetails']['receiverDetails']['typeCode'] = (string) $this->receiverTypeCode;
        }

        if (isset($this->localProductCode) && $this->localProductCode !== '') {
            $query['localProductCode'] = $this->localProductCode;
        }

        if ($this->receiverContact->getEmail() !== '') {
            $query['shipmentNotification'][] = [
                'typeCode' => 'email',
                'languageCountryCode' => $this->receiverAddress->getCountryCode(),
                'receiverId' => $this->receiverContact->getEmail(),
            ];
        }

        if ($this->isPickupRequested) {
            $query['pickup'] = [
                'isRequested' => $this->isPickupRequested,
                'closeTime' => $this->pickupCloseTime,
                'location' => $this->pickupLocation,
            ];

            $query['pickup']['pickupDetails'] = [
                'postalAddress' => $this->pickupAddress->getAsArray(),
                'contactInformation' => $this->pickupContact->getAsArray(),
            ];
        }

        return $query;
    }

    private function prepareAccountsQuery(): array
    {
        $accounts = [];

        /** @var Account $account */
        foreach ($this->accounts as $account) {
            $accounts[] = $account->getAsArray();
        }

        return $accounts;
    }

    private function preparePackagesQuery(): array
    {
        $packages = [];

        foreach ($this->packages as $package) {
            $packages[] = [
                'weight' => $package->getWeight(),
                'dimensions' => [
                   'length' => $package->getLength(),
                   'width' => $package->getWidth(),
                   'height' => $package->getHeight(),
                ],
            ];
        }

        return $packages;
    }

    /**
     * @return void
     * @throws MissingArgumentException
     */
    private function validateParams(): void
    {
        if (!isset($this->incoterm)) {
            $this->incoterm = new Incoterm('');
        }

        foreach ($this->requiredArguments as $param) {
            if (!isset($this->{$param})) {
                throw new MissingArgumentException("Missing argument: {$param}");
            }
        }
    }
}
