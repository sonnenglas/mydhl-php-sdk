<?php

declare(strict_types=1);

namespace Sonnenglas\MyDHL;

use Sonnenglas\MyDHL\Services\ImageService;
use Sonnenglas\MyDHL\Services\PickupService;
use Sonnenglas\MyDHL\Services\ProofOfDeliveryService;
use Sonnenglas\MyDHL\Services\RateService;
use Sonnenglas\MyDHL\Services\ShipmentService;
use Sonnenglas\MyDHL\Services\TrackingService;

class MyDHL
{
    protected Client $client;

    public function __construct(
        string $username,
        string $password,
        bool $testMode = false,
    ) {
        $this->client = new Client($username, $password, $testMode);
    }

    public function getRateService(): RateService
    {
        return new RateService($this->client);
    }

    public function getShipmentService(): ShipmentService
    {
        return new ShipmentService($this->client);
    }

    public function getTrackingService(): TrackingService
    {
        return new TrackingService($this->client);
    }

    public function getPickupService(): PickupService
    {
        return new PickupService($this->client);
    }

    public function getImageService(): ImageService
    {
        return new ImageService($this->client);
    }

    public function getProofOfDeliveryService(): ProofOfDeliveryService
    {
        return new ProofOfDeliveryService($this->client);
    }
}
