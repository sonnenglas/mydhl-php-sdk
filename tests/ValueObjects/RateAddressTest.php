<?php

declare(strict_types=1);

namespace Tests\ValueObjects;

use Sonnenglas\MyDHL\Exceptions\InvalidAddressException;
use Sonnenglas\MyDHL\ValueObjects\RateAddress;
use Tests\TestCase;

class RateAddressTest extends TestCase
{
    public function testValidRateAddress(): void
    {
        $countryCode = "DE";
        $postalCode = "10245";
        $cityName = "Berlin";

        // Invalid address because the country code must be 2 characters long
        $address = new RateAddress(
            countryCode: $countryCode,
            postalCode: $postalCode,
            cityName: $cityName
        );

        $this->assertEquals($countryCode, $address->getCountryCode());
        $this->assertEquals($postalCode, $address->getPostalCode());
        $this->assertEquals($cityName, $address->getCityName());
    }

    public function testInvalidRateAddress(): void
    {
        $this->expectException(InvalidAddressException::class);

        // Invalid address because the country code must be 2 characters long
        $address = new RateAddress(
            countryCode: "USA",
            postalCode: "10001",
            cityName: "New York"
        );
    }
}
