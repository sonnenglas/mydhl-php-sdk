<?php

declare(strict_types=1);

namespace Sonnenglas\MyDHL\ValueObjects;

use DateTimeImmutable;
use Sonnenglas\MyDHL\Exceptions\InvalidArgumentException;
use Sonnenglas\MyDHL\Exceptions\MissingArgumentException;

/**
 * Standalone pickup booking request for `POST /pickups`. Used to schedule a
 * courier when the shipment was created without `pickup.isRequested = true`.
 */
final class PickupRequest
{
    public const LOCATION_BUSINESS = 'business';
    public const LOCATION_RESIDENCE = 'residence';

    /**
     * @param array<int, mixed> $accounts Validated at runtime to be a list of Account.
     * @param array<int, mixed> $shipmentDetails Validated at runtime to be a list of PickupShipmentSummary.
     */
    public function __construct(
        public readonly DateTimeImmutable $plannedPickupDateAndTime,
        public readonly array $accounts,
        public readonly Address $shipperAddress,
        public readonly Contact $shipperContact,
        public readonly array $shipmentDetails,
        public readonly string $closeTime = '',
        public readonly string $location = '',
        public readonly ?string $locationType = null,
        public readonly ?string $remark = null,
        public readonly ?Address $pickupAddress = null,
        public readonly ?Contact $pickupContact = null,
    ) {
        if ($accounts === []) {
            throw new MissingArgumentException('PickupRequest requires at least one account.');
        }

        foreach ($accounts as $account) {
            if (!$account instanceof Account) {
                throw new InvalidArgumentException('accounts must contain only Account instances.');
            }
        }

        if ($shipmentDetails === []) {
            throw new MissingArgumentException('PickupRequest requires at least one shipment detail.');
        }

        foreach ($shipmentDetails as $detail) {
            if (!$detail instanceof PickupShipmentSummary) {
                throw new InvalidArgumentException(
                    'shipmentDetails must contain only PickupShipmentSummary instances.',
                );
            }
        }

        if ($shipperContact->getPhone() === '') {
            throw new MissingArgumentException('Shipper contact must include a phone number for pickup.');
        }

        if ($locationType !== null && $locationType !== self::LOCATION_BUSINESS && $locationType !== self::LOCATION_RESIDENCE) {
            throw new InvalidArgumentException("locationType must be 'business' or 'residence'.");
        }

        if (($pickupAddress === null) !== ($pickupContact === null)) {
            throw new InvalidArgumentException(
                'pickupAddress and pickupContact must be set together (or both omitted).',
            );
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function toQuery(): array
    {
        $accounts = [];
        foreach ($this->accounts as $account) {
            assert($account instanceof Account);
            $accounts[] = $account->getAsArray();
        }

        $shipmentDetails = [];
        foreach ($this->shipmentDetails as $detail) {
            assert($detail instanceof PickupShipmentSummary);
            $shipmentDetails[] = $detail->toArray();
        }

        $customerDetails = [
            'shipperDetails' => [
                'postalAddress' => $this->shipperAddress->getAsArray(),
                'contactInformation' => $this->shipperContact->getAsArray(),
            ],
        ];

        if ($this->pickupAddress !== null && $this->pickupContact !== null) {
            $customerDetails['pickupDetails'] = [
                'postalAddress' => $this->pickupAddress->getAsArray(),
                'contactInformation' => $this->pickupContact->getAsArray(),
            ];
        }

        return array_filter([
            'plannedPickupDateAndTime' => $this->plannedPickupDateAndTime->format('Y-m-d\TH:i:s \G\M\TP'),
            'closeTime' => $this->closeTime !== '' ? $this->closeTime : null,
            'location' => $this->location !== '' ? $this->location : null,
            'locationType' => $this->locationType,
            'remark' => $this->remark,
            'accounts' => $accounts,
            'customerDetails' => $customerDetails,
            'shipmentDetails' => $shipmentDetails,
        ], static fn (mixed $v): bool => $v !== null);
    }
}
