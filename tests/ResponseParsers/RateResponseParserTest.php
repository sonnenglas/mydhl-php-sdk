<?php

declare(strict_types=1);

namespace Tests\ResponseParsers;

use DateTimeImmutable;
use Sonnenglas\MyDHL\ResponseParsers\RateResponseParser;
use Tests\TestCase;

final class RateResponseParserTest extends TestCase
{
    public function testParseTotalPrice(): void
    {
        $rateResponseParser = new RateResponseParser([]);

        $expectedResult = [16.64, 'EUR'];

        $rate = self::loadJsonFixture('fixtures/rate.json');

        /** @var list<array<string, mixed>> $totalPrices */
        $totalPrices = $rate['totalPrice'];

        $result = $this->executePrivateMethod($rateResponseParser, 'parseTotalPrice', [$totalPrices]);

        self::assertSame($expectedResult, $result);
    }

    public function testParseRate(): void
    {
        $rateResponseParser = new RateResponseParser([]);

        $fakeRate = self::loadJsonFixture('fixtures/rate.json');

        $rate = $this->executePrivateMethod($rateResponseParser, 'parseRate', [$fakeRate]);

        $expectedEstimatedDeliveryDate = new DateTimeImmutable('2021-01-19T12:00:00');
        $expectedPricingDate = new DateTimeImmutable('2022-01-14 00:00:00');

        self::assertInstanceOf(\Sonnenglas\MyDHL\ValueObjects\Rate::class, $rate);
        self::assertSame('EXPRESS DOMESTIC 12:00', $rate->getProductName());
        self::assertSame('1', $rate->getProductCode());
        self::assertSame('L', $rate->getLocalProductCode());
        self::assertSame('DE', $rate->getLocalProductCountryCode());
        self::assertFalse($rate->getIsCustomerAgreement());
        self::assertSame(3.6, $rate->getWeightVolumetric());
        self::assertSame(4.0, $rate->getWeightProvided());
        self::assertSame(16.64, $rate->getTotalPrice());
        self::assertSame('EUR', $rate->getCurrency());
        self::assertEquals($expectedEstimatedDeliveryDate, $rate->getEstimatedDeliveryDateAndTime());
        self::assertEquals($expectedPricingDate, $rate->getPricingDate());
    }

    public function testParseRateFallsBackToVolumetricWhenProvidedMissing(): void
    {
        $rateResponseParser = new RateResponseParser([]);

        $fakeRate = self::loadJsonFixture('fixtures/rate.json');
        /** @var array<string, mixed> $weight */
        $weight = $fakeRate['weight'];
        unset($weight['provided']);
        $fakeRate['weight'] = $weight;

        $rate = $this->executePrivateMethod($rateResponseParser, 'parseRate', [$fakeRate]);

        self::assertInstanceOf(\Sonnenglas\MyDHL\ValueObjects\Rate::class, $rate);
        self::assertSame(3.6, $rate->getWeightVolumetric());
        self::assertSame(3.6, $rate->getWeightProvided(), 'Should fall back to volumetric weight when DHL omits "provided".');
    }

    public function testParse(): void
    {
        $expectedProductNames = [
            'EXPRESS DOMESTIC 9:00',
            'EXPRESS DOMESTIC 10:00',
            'EXPRESS DOMESTIC 12:00',
            'MEDICAL EXPRESS DOMESTIC',
            'EXPRESS EASY DOC',
        ];

        $fakeResponse = self::loadJsonFixture('fixtures/get_rates.json');

        $rateResponseParser = new RateResponseParser($fakeResponse);

        $rates = $rateResponseParser->parse();

        self::assertCount(5, $rates);

        foreach ($rates as $rate) {
            self::assertContains($rate->getProductName(), $expectedProductNames);
        }
    }
}
