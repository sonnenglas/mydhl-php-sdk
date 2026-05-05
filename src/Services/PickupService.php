<?php

declare(strict_types=1);

namespace Sonnenglas\MyDHL\Services;

use Sonnenglas\MyDHL\Client;
use Sonnenglas\MyDHL\Exceptions\ClientException;
use Sonnenglas\MyDHL\Exceptions\InvalidArgumentException;
use Sonnenglas\MyDHL\ResponseParsers\PickupResponseParser;
use Sonnenglas\MyDHL\ValueObjects\PickupBooking;
use Sonnenglas\MyDHL\ValueObjects\PickupRequest;

class PickupService
{
    private const PICKUPS_URL = 'pickups';

    /** @var array<string, mixed> */
    private array $lastResponse = [];

    public function __construct(private readonly Client $client)
    {
    }

    /**
     * Schedule a courier pickup. Use when the shipment was created with
     * `Pickup::notRequested()` and the pickup needs to be booked separately.
     *
     * @throws ClientException
     */
    public function book(PickupRequest $request): PickupBooking
    {
        $this->lastResponse = $this->client->post(self::PICKUPS_URL, $request->toQuery());

        return (new PickupResponseParser($this->lastResponse))->parse();
    }

    /**
     * Cancel a previously booked pickup.
     *
     * @throws ClientException
     */
    public function cancel(string $dispatchConfirmationNumber, string $requestorName, string $reason): void
    {
        if ($dispatchConfirmationNumber === '') {
            throw new InvalidArgumentException('dispatchConfirmationNumber must not be empty.');
        }

        if ($requestorName === '') {
            throw new InvalidArgumentException('requestorName must not be empty.');
        }

        if ($reason === '') {
            throw new InvalidArgumentException('reason must not be empty.');
        }

        $this->lastResponse = $this->client->delete(
            self::PICKUPS_URL . '/' . rawurlencode($dispatchConfirmationNumber),
            [
                'requestorName' => $requestorName,
                'reason' => $reason,
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function getLastRawResponse(): array
    {
        return $this->lastResponse;
    }
}
