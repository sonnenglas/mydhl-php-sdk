<?php

declare(strict_types=1);

namespace Sonnenglas\MyDHL\ResponseParsers;

use Sonnenglas\MyDHL\Exceptions\TotalPriceNotFoundException;
use Sonnenglas\MyDHL\ValueObjects\Rate;

class RateResponseParser
{
    public function parse(array $response): array
    {
        return $response;
    }

    /**
     * @param array $response
     * @return Rate[]
     */
    protected function extractRates(array $response): array
    {
        $rates = [];

        if (!isset($response['products']) || !is_iterable($response['products'])) {
            return $rates;
        }

        foreach ($response['products'] as $p) {
            $rates[] = $this->parseRate($p);
        }

        return $rates;
    }

    protected function parseRate(array $rate): Rate
    {
        [$totalPrice, $currency] = $this->parseTotalPrice($rate['totalPrice']);
        return new Rate(
            productName: (string) $rate['productName'],
            productCode: (string) $rate['productCode'],
            isCustomerAgreement: (bool) $rate['isCustomerAgreement'],
            weightVolumetric: (float) $rate['weight']['volumetric'],
            weightProvided: (float) $rate['weight']['provided'],
            totalPrice: (float) $totalPrice,
            currency: (string) $currency,
        );
    }

    protected function parseTotalPrice(array $prices): array
    {
        foreach ($prices as $price) {
            if ($price['currencyType'] === 'BILLC') {
                return [(float) $price['price'], (string) $price['priceCurrency']];
            }
        }

        throw new TotalPriceNotFoundException('Total price of type BILLC not found.');
    }

}
