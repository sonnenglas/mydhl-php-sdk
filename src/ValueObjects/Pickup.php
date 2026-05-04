<?php

declare(strict_types=1);

namespace Sonnenglas\MyDHL\ValueObjects;

use Sonnenglas\MyDHL\Exceptions\MissingArgumentException;

final class Pickup
{
    public function __construct(
        public readonly bool $isRequested,
        public readonly string $closeTime = '',
        public readonly string $location = '',
        public readonly ?Address $address = null,
        public readonly ?Contact $contact = null,
    ) {
        if ($this->isRequested && ($this->address === null || $this->contact === null)) {
            throw new MissingArgumentException('Pickup address and contact are required when pickup is requested.');
        }
    }

    public static function notRequested(): self
    {
        return new self(isRequested: false);
    }

    /**
     * @return array<string, mixed>
     */
    public function toQuery(): array
    {
        if (!$this->isRequested || $this->address === null || $this->contact === null) {
            return ['isRequested' => false];
        }

        return [
            'isRequested' => true,
            'closeTime' => $this->closeTime,
            'location' => $this->location,
            'pickupDetails' => [
                'postalAddress' => $this->address->getAsArray(),
                'contactInformation' => $this->contact->getAsArray(),
            ],
        ];
    }
}
