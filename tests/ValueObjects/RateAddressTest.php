<?php

declare(strict_types=1);

namespace Tests\ValueObjects;

use Sonnenglas\MyDHL\Exceptions\InvalidAddressException;
use Sonnenglas\MyDHL\ValueObjects\RateAddress;
use Tests\TestCase;

final class RateAddressTest extends TestCase
{
    public function testValidRateAddress(): void
    {
        $countryCode = 'DE';
        $postalCode = '10245';
        $cityName = 'Berlin';

        $address = new RateAddress(
            countryCode: $countryCode,
            postalCode: $postalCode,
            cityName: $cityName,
        );

        self::assertSame($countryCode, $address->getCountryCode());
        self::assertSame($postalCode, $address->getPostalCode());
        self::assertSame($cityName, $address->getCityName());
    }

    public function testInvalidRateAddress(): void
    {
        $this->expectException(InvalidAddressException::class);

        new RateAddress(
            countryCode: 'USA',
            postalCode: '10001',
            cityName: 'New York',
        );
    }
}
