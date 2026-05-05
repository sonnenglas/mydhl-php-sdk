<?php

declare(strict_types=1);

namespace Sonnenglas\MyDHL\Services;

use Sonnenglas\MyDHL\Client;
use Sonnenglas\MyDHL\Exceptions\ClientException;
use Sonnenglas\MyDHL\ResponseParsers\ShipmentResponseParser;
use Sonnenglas\MyDHL\ValueObjects\Shipment;
use Sonnenglas\MyDHL\ValueObjects\ShipmentRequest;

class ShipmentService
{
    private const CREATE_SHIPMENT_URL = 'shipments';

    /** @var array<string, mixed> */
    private array $lastResponse = [];

    public function __construct(private readonly Client $client)
    {
    }

    /**
     * @throws ClientException
     */
    public function createShipment(ShipmentRequest $request): Shipment
    {
        $this->lastResponse = $this->client->post(self::CREATE_SHIPMENT_URL, $request->toQuery());

        return (new ShipmentResponseParser($this->lastResponse))->parse();
    }

    /**
     * @return array<string, mixed>
     */
    public function getLastRawResponse(): array
    {
        return $this->lastResponse;
    }
}
