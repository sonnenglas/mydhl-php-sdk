<?php

declare(strict_types=1);

namespace Tests\ResponseParsers;

use DateTimeImmutable;
use Sonnenglas\MyDHL\ResponseParsers\RateResponseParser;
use Sonnenglas\MyDHL\ValueObjects\Rate;
use Tests\TestCase;

class RateResponseParserTest extends TestCase
{
    private RateResponseParser $rateResponseParser;

    public function setUp(): void
    {
        $this->rateResponseParser = new RateResponseParser();
    }

    public function testParseTotalPrice(): void
    {
        $expectedResult = [16.64, "EUR"];

        /** @var Rate $rate */
        $rate = json_decode(file_get_contents(__DIR__ . "/../fixtures/rate.json"), true);

        $totalPrices = $rate['totalPrice'];

        $result = $this->executePrivateMethod($this->rateResponseParser, 'parseTotalPrice', [$totalPrices]);

        $this->assertEquals($expectedResult, $result);
    }

    public function testParseRate(): void
    {
        $fakeRate = json_decode(file_get_contents(__DIR__ . "/../fixtures/rate.json"), true);

        /** @var Rate $rate */
        $rate = $this->executePrivateMethod($this->rateResponseParser, 'parseRate', [$fakeRate]);

        $this->assertTrue($rate instanceof Rate);

        $expectedEstimatedDeliveryDate =  new DateTimeImmutable('2021-01-19T12:00:00');

        $expectedPricingDate = new DateTimeImmutable('2022-01-14 00:00:00');

        $this->assertEquals("EXPRESS DOMESTIC 12:00", $rate->getProductName());
        $this->assertEquals("1", $rate->getProductCode());
        $this->assertEquals("L", $rate->getLocalProductCode());
        $this->assertEquals("DE", $rate->getLocalProductCountryCode());
        $this->assertEquals(false, $rate->getIsCustomerAgreement());
        $this->assertEquals(3.6, $rate->getWeightVolumetric());
        $this->assertEquals(4, $rate->getWeightProvided());
        $this->assertEquals(16.64, $rate->getTotalPrice());
        $this->assertEquals("EUR", $rate->getCurrency());
        $this->assertEquals($expectedEstimatedDeliveryDate, $rate->getEstimatedDeliveryDateAndTime());
        $this->assertEquals($expectedPricingDate, $rate->getPricingDate());
    }

    public function testParse(): void
    {
        $expectedProductNames = [
            "EXPRESS DOMESTIC 9:00",
            "EXPRESS DOMESTIC 10:00",
            "EXPRESS DOMESTIC 12:00",
            "MEDICAL EXPRESS DOMESTIC",
            "EXPRESS EASY DOC",
        ];

        $fakeResponse = file_get_contents(__DIR__ . "/../fixtures/get_rates.json");
        $fakeResponse = json_decode($fakeResponse, true);
        $rates = (new RateResponseParser())->parse($fakeResponse);

        $this->assertCount(5, $rates);

        foreach ($rates as $rate) {
            $this->assertTrue(in_array($rate->getProductName(), $expectedProductNames, true));
        }
    }
}
