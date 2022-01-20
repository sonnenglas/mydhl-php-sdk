<?php

declare(strict_types=1);

namespace Tests\ResponseParsers;

use Sonnenglas\MyDHL\ResponseParsers\RateResponseParser;
use Sonnenglas\MyDHL\ValueObjects\Rate;
use Tests\TestCase;

class RateResponseParserTest extends TestCase
{
    private RateResponseParser $rateResponseParser;

    public function setUp()
    {
        $this->rateResponseParser = new RateResponseParser($fakeResponse)
    }

    public function testParseRate(): void
    {
        $fakeRate = file_get_contents(__DIR__ . "/../fixtures/rate.json");

        $this->executePrivateMethod
    }

//    public function testParse(): void
//    {
//        $fakeResponse = file_get_contents(__DIR__ . "/../fixtures/get_rates.json");
//        $fakeResponse = json_decode($fakeResponse, true);
//        $parsedResponse = (new RateResponseParser($fakeResponse))->parse();
//        $this->assertEquals([], $parsedResponse);
//    }
}
