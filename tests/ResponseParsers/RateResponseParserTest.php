<?php

declare(strict_types=1);

namespace Tests\ResponseParsers;

use Sonnenglas\MyDHL\ResponseParsers\RateResponseParser;
use Tests\TestCase;

class RateResponseParserTest extends TestCase
{
    public function testParse(): void
    {
        $fakeResponse = file_get_contents(__DIR__ . "/../fixtures/get_rates.json");
        $fakeResponse = json_decode($fakeResponse, true);
        $parsedResponse = (new RateResponseParser($fakeResponse))->parse();
        $this->assertEquals([], $parsedResponse);
    }
}
