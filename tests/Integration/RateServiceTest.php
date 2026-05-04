<?php

declare(strict_types=1);

namespace Tests\Integration;

use DateTimeImmutable;
use Sonnenglas\MyDHL\ValueObjects\Package;
use Sonnenglas\MyDHL\ValueObjects\RateAddress;
use Sonnenglas\MyDHL\ValueObjects\RateRequest;

final class RateServiceTest extends IntegrationTestCase
{
    public function testReturnsDomesticDeRates(): void
    {
        $request = new RateRequest(
            accountNumber: $this->accountNumber,
            originAddress: new RateAddress(countryCode: 'DE', postalCode: '10117', cityName: 'Berlin'),
            destinationAddress: new RateAddress(countryCode: 'DE', postalCode: '20099', cityName: 'Hamburg'),
            package: new Package(weight: 5, height: 20, length: 10, width: 30),
            shippingDate: new DateTimeImmutable('+2 weekdays'),
        );

        $rates = $this->myDhl->getRateService()->getRates($request);

        self::assertNotEmpty($rates, 'Sandbox should return at least one product for a valid DE→DE rate request.');

        foreach ($rates as $rate) {
            self::assertNotSame('', $rate->getProductCode());
            self::assertSame('EUR', $rate->getCurrency());
            self::assertGreaterThan(0.0, $rate->getTotalPrice());
        }
    }
}
