<?php

declare(strict_types=1);

namespace Sonnenglas\MyDHL\Services;


use Sonnenglas\MyDHL\Client;

class ShipmentService
{
    private const CREATE_SHIPMENT_URL = 'shipments';

    public function __construct(private Client $client)
    {
    }
}