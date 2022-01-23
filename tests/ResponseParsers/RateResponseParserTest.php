<?php

declare(strict_types=1);

namespace Tests\ResponseParsers;

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
        $expectedResult = [53.25, "EUR"];

        $rate = json_decode(file_get_contents(__DIR__ . "/../fixtures/rate.json"), true);

        $totalPrices = $rate['totalPrice'];

        $result = $this->executePrivateMethod($this->rateResponseParser, 'parseTotalPrice', [$totalPrices]);

        $this->assertEquals($expectedResult, $result);
    }

    public function testParseRate(): void
    {
        $fakeRate = json_decode(file_get_contents(__DIR__ . "/../fixtures/rate.json"), true);

        $result = $this->executePrivateMethod($this->rateResponseParser, 'parseRate', [$fakeRate]);

        $this->assertTrue($result instanceof Rate);
    }

//    public function testParse(): void
//    {
//        $fakeResponse = file_get_contents(__DIR__ . "/../fixtures/get_rates.json");
//        $fakeResponse = json_decode($fakeResponse, true);
//        $parsedResponse = (new RateResponseParser($fakeResponse))->parse();
//        $this->assertEquals([], $parsedResponse);
//    }
}
